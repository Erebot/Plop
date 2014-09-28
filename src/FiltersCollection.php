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

namespace Plop;

/**
 *  \brief
 *      A class that can be used to filter records.
 */
class FiltersCollection implements \Plop\FiltersCollectionInterface
{
    /// List of filters.
    protected $filters;

    /// Construct a new collection of filters.
    public function __construct()
    {
        $this->filters = array();
    }

    /// \copydoc Countable::count().
    public function count()
    {
        return count($this->filters);
    }

    /// \copydoc IteratorAggregate::getIterator().
    public function getIterator()
    {
        return new ArrayIterator($this->filters);
    }

    /// \copydoc ArrayAccess::offsetGet().
    public function offsetGet($offset)
    {
        return $this->filters[$offset];
    }

    /// \copydoc ArrayAccess::offsetSet().
    public function offsetSet($offset, $value)
    {
        $key = array_search($value, $this->filters, true);
        if ($key === false) {
            if ($offset === null) {
                $this->filters[] = $value;
            } else {
                $this->filters[$offset] = $value;
            }
        }
    }

    /// \copydoc ArrayAccess::offsetExists().
    public function offsetExists($offset)
    {
        $res = isset($this->filters[$offset]) ||
               (array_search($value, $this->filters, true) !== false);
        return $res;
    }

    /// \copydoc ArrayAccess::offsetUnset().
    public function offsetUnset($offset)
    {
        if (!is_int($offset)) {
            $offset = array_search($offset, $this->filters, true);
        }
        if (isset($this->filters[$offset])) {
            unset($this->filters[$offset]);
        }
    }

    /// \copydoc Plop::FiltersCollectionInterface::filter().
    public function filter(\Plop\RecordInterface $record)
    {
        foreach ($this->filters as $filter) {
            if (!$filter->filter($record)) {
                return false;
            }
        }
        return true;
    }
}
