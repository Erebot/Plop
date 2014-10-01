<?php

namespace Plop\Interpolator;

class Psr3 implements \Plop\InterpolatorInterface
{
    function interpolate($message, array $args = array())
    {
        $replace = array();
        foreach ($args as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        return strtr($message, $replace);
    }
}
