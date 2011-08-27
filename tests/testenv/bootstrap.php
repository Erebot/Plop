<?php
/*
    This file is part of Erebot.

    Erebot is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    Erebot is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Erebot.  If not, see <http://www.gnu.org/licenses/>.
*/

// Avoid harmless warning on some
// badly-configured PHP installations.
date_default_timezone_set('UTC');

if ('@php_dir@' == '@'.'php_dir'.'@') {
    $base = dirname(dirname(TESTENV_DIR . DIRECTORY_SEPARATOR)) .
            DIRECTORY_SEPARATOR;
    require(dirname(__FILE__).DIRECTORY_SEPARATOR.'Autoload.php');

    // Add the component's sources to the Autoloader.
    Erebot_Autoload::initialize($base . "src");

    // Add vendor sources too.
    $base .= "vendor";
    if (is_dir($base)) {
        foreach (scandir($base) as $path) {
            if (trim($path, '.') == '')
                continue;
            $path = $base . DIRECTORY_SEPARATOR .
                    $path . DIRECTORY_SEPARATOR;
            if (is_dir($path . 'src'))
                Erebot_Autoload::initialize($path . 'src');
            if (is_dir($path . 'lib'))  // for sfService.
                Erebot_Autoload::initialize($path . 'lib');
        }
    }
    // Register include_path with the Autoloader.
    foreach (explode(PATH_SEPARATOR, get_include_path()) as $path)
        Erebot_Autoload::initialize($path);
}
// Otherwise, we're probably in Pyrus/PEAR.
else {
    require('Erebot/Autoload.php');
    Erebot_Autoload::initialize('@php_dir@');
}

