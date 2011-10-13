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

class   Plop_RootLogger
extends Plop_Logger
{
    public function __construct($level)
    {
        parent::__construct("root", $level);
    }
}

if (Plop_Logger::$root === NULL) {
    Plop_Logger::$root     = new Plop_RootLogger(Plop::WARNING);
    Plop_Logger::$manager  = new Plop_Manager(Plop_Logger::$root);
}

