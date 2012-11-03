<?php
/*
    This file is part of Plop, a simple logging library for PHP.

    Copyright © 2010-2012 François Poirotte

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

/**
 *  \brief
 *      An autoloader for Plop's classes.
 */
class Plop_Autoloader
{
    /// Singleton to prevent multiple registrations.
    static protected $_instance;

    /// Prevent direct construction of this class.
    protected function __construct()
    {
    }

    /// This class may not be cloned.
    public function __clone()
    {
        throw new Exception('Cloning this class is forbidden');
    }

    /**
     * Register this class as an SPL auto-loader.
     *
     * \return
     *      This method does not return any value.
     */
    static public function register()
    {
        if (self::$_instance === NULL) {
            $cls = __CLASS__;
            self::$_instance = new $cls();
            spl_autoload_register(array(self::$_instance, 'load'));
        }
    }

    /**
     * Attempt to load the given class.
     *
     * \param string $class
     *      Name of the class to load.
     *
     * \note
     *      This method only attempts to load Plop's classes.
     *      It will return \a FALSE if an attempt is made to
     *      load a class that does not belong to Plop.
     *
     * \retval bool
     *      Whether the given class was successfully loaded
     *      (\a TRUE) or not (\a FALSE).
     */
    public function load($class)
    {
        if (strncasecmp($class, 'Plop', 4))
            return FALSE;

        if (strpos($class, '://') !== FALSE) {
            throw new Exception('Possible exploitation attempt detected');
        }

        $class = str_replace(array('_', '\\'), DIRECTORY_SEPARATOR, $class);
        $file = dirname(dirname(__FILE__)) .
                DIRECTORY_SEPARATOR . $class . '.php';
        require_once($file);
        return TRUE;
    }
}


