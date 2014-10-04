<?php
/*
    This file is part of Plop, a simple logging library for PHP.

    Copyright © 2010-2014 François Poirotte

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

if (version_compare(phpversion(), '5.3.3', '<')) {
    echo basename(__FILE__) . " requires PHP 5.3.3 or newer." . PHP_EOL;
    exit(1);
}
foreach (array('phar', 'spl', 'pcre', 'simplexml') as $ext) {
    if (!extension_loaded($ext)) {
        echo "Extension $ext is required." . PHP_EOL;
        exit(1);
    }
}
try {
    Phar::mapPhar();
} catch (Exception $e) {
    echo "Cannot process " . basename(__FILE__, '.phar') . ":" . PHP_EOL;
    echo "\t" . $e->getMessage() . PHP_EOL;
    exit(1);
}

require_once('phar://' . __FILE__ .
    DIRECTORY_SEPARATOR . 'src' .
    DIRECTORY_SEPARATOR . 'Autoloader.php'
);
\Plop\Autoloader::register();

__HALT_COMPILER();
