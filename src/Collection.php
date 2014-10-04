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

namespace Plop;

/**
 *  \brief
 *      A class that can be used to handle collections of objects.
 */
class Collection implements \ArrayAccess, \Countable, \IteratorAggregate
{
    const TYPE_HINT = null;

    /// List of items in the collection.
    protected $items = array();

    /// \copydoc Countable::count().
    public function count()
    {
        return count($this->items);
    }

    /// \copydoc IteratorAggregate::getIterator().
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /// \copydoc ArrayAccess::offsetGet().
    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    /// \copydoc ArrayAccess::offsetSet().
    public function offsetSet($offset, $value)
    {
        $hint = static::TYPE_HINT;
        if ($hint !== null && (!is_object($value) || !($value instanceof $hint))) {
            throw new \Plop\Exception('An instance of ' . static::TYPE_HINT . ' was expected');
        }

        if ($offset === null) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    /// \copydoc ArrayAccess::offsetExists().
    public function offsetExists($offset)
    {
        $res = @isset($this->items[$offset]) ||
               (array_search($offset, $this->items, true) !== false);
        return $res;
    }

    /// \copydoc ArrayAccess::offsetUnset().
    public function offsetUnset($offset)
    {
        if (!is_int($offset)) {
            $offset = array_search($offset, $this->items, true);
        }
        if (isset($this->items[$offset])) {
            unset($this->items[$offset]);
        }
    }
}
