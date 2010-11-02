<?php
/*
    This file is part of Plop.

    Plop is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Plop is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Plop.  If not, see <http://www.gnu.org/licenses/>.
*/

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

