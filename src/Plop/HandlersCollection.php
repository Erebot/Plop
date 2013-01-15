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
 *      A class that can be used to filter records.
 */
class       Plop_HandlersCollection
implements  Plop_HandlersCollectionInterface
{
    /// List of handlers.
    protected $_handlers;

    /// Construct a new collection of handlers.
    public function __construct()
    {
        $this->_handlers = array();
    }

    /// \copydoc Countable::count().
    public function count()
    {
        return count($this->_handlers);
    }

    /// \copydoc IteratorAggregate::getIterator().
    public function getIterator()
    {
        return new ArrayIterator($this->_handlers);
    }

    /// \copydoc ArrayAccess::offsetGet().
    public function offsetGet($offset)
    {
        return $this->_handlers[$offset];
    }

    /// \copydoc ArrayAccess::offsetSet().
    public function offsetSet($offset, $value)
    {
        $key = array_search($value, $this->_handlers, TRUE);
        if ($key === FALSE) {
            if ($offset === NULL) {
                $this->_handlers[] = $value;
            }
            else {
                $this->_handlers[$offset] = $value;
            }
        }
    }

    /// \copydoc ArrayAccess::offsetExists().
    public function offsetExists($offset)
    {
        $res = isset($this->_handlers[$offset]) ||
               (array_search($value, $this->_handlers, TRUE) !== FALSE);
        return $res;
    }

    /// \copydoc ArrayAccess::offsetUnset().
    public function offsetUnset($offset)
    {
        if (!is_int($offset)) {
            $offset = array_search($offset, $this->_handlers, TRUE);
        }
        if (isset($this->_handlers[$offset])) {
            unset($this->_handlers[$offset]);
        }
    }

    /// \copydoc Plop_HandlersCollectionInterface::handle().
    public function handle(Plop_RecordInterface $record)
    {
        foreach ($this->_handlers as $handler) {
            $handler->handle($record);
        }
        return $this;
    }
}

