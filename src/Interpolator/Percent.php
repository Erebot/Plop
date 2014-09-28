<?php

namespace Plop\Interpolator;

class Percent implements \Plop\InterpolatorInterface
{
    /**
     * Return a percent-prefixed variable.
     *
     * \param string $a
     *      Variable to work on.
     *
     * \retval string
     *      Percent-prefixed version of the variable name.
     *
     * @codeCoverageIgnore
     */
    private static function pctPrefix($a)
    {
        return '%('.$a.')';
    }

    /**
     * Return an incremented and percent-prefixed variable.
     *
     * \param int $a
     *      Variable to work on.
     *
     * \retval string
     *      Incremented and percent-prefixed version
     *      of the variable.
     *
     * @codeCoverageIgnore
     */
    private static function increment($a)
    {
        return '%'.($a + 1).'$';
    }

    /// \copydoc Plop::InterpolatorInterface::interpolate().
    public function interpolate($msg, array $args = array())
    {
        preg_match_all('/(?<!%)(?:%%)*%\\(([^\\)]*)\\)/', $msg, $matches);
        // Only define the variables if there are any.
        if (isset($matches[1][0])) {
            $args += array_combine(
                $matches[1],
                array_fill(0, count($matches[1]), null)
            );
        }

        if (!count($args)) {
            return $msg;
        }

        // Mapping = array(name => index)
        $keys       = array_keys($args);
        $mapping    = array_flip($keys);
        $keys       = array_map(array('static', 'pctPrefix'), $keys);
        $values     = array_map(array('static', 'increment'), $mapping);
        $mapping    = array_combine($keys, $values);
        $msg        = strtr($msg, $mapping);
        return vsprintf($msg, array_values($args));
    }
}
