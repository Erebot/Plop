<?php

namespace Plop\Interpolator;

class Percent implements \Plop\InterpolatorInterface
{
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
        $keys       = array_map(function ($key) { return "%($key)"; }, $keys);
        $values     = array_map(function ($val) { return '%'.($val + 1).'$'; }, $mapping);
        $mapping    = array_combine($keys, $values);
        $msg        = strtr($msg, $mapping);
        return vsprintf($msg, array_values($args));
    }
}
