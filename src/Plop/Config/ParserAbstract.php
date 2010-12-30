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

abstract class Plop_Config_ParserAbstract
{
    protected $_logging;

    public function __construct(Plop &$logging, $fname)
    {
        $this->_logging =& $logging;
        $this->cp       =  $this->getConfigParserData($fname);
    }

    protected function createHandlerInstance($cls, &$args)
    {
        if (
            !class_exists($cls) ||
            !is_subclass_of($cls, 'Plop_Handler')
            )
            throw new Exception(sprintf('No such class (%s)', $cls));

        // call_user_func_array doesn't work with constructors.
        // We use the reflection API instead, which allows a ctor
        // to be called with a variable number of args.
        $reflector = new ReflectionClass($cls);
        return $reflector->newInstanceArgs($args);
    }

    public function doWork($disableExistingLoggers=TRUE)
    {
        $formatters = $this->createFormatters();
        $handlers = $this->installHandlers($formatters);
        $this->installLoggers($handlers, $disableExistingLoggers);
    }

    abstract protected function getConfigParserData($fname);
    abstract protected function createFormatters();
    abstract protected function installHandlers($formatters);
    abstract protected function installLoggers($handlers, $disableExistingLoggers);
}

