<?php

if (!defined('__DIR__')) {
  class __FILE_CLASS__ {
    function  __toString() {
      $X = debug_backtrace();
      return dirname($X[1]['file']);
    }
  }
  define('__DIR__', new __FILE_CLASS__);
} 

set_include_path(
    dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . PATH_SEPARATOR .
    get_include_path()
);

function autoload_plop($class)
{
    $class = ltrim($class, '\\');
    if (strncasecmp($class, 'pear2\\', 6))
        return FALSE;
    $className = substr($class, 6);
    $fname = str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $className) . '.php';
    include($fname);
    return (class_exists($class) || interface_exists($class));
}

spl_autoload_register('autoload_plop');

