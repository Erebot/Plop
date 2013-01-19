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

class       Plop_PrefixesCollection
implements  Plop_PrefixesCollectionInterface
{
    /// List of prefixes.
    protected $_prefixes;

    /// Construct a new collection of prefixes.
    public function __construct()
    {
        $this->_prefixes = array();
    }

    /// \copydoc Countable::count().
    public function count()
    {
        return count($this->_prefixes, COUNT_RECURSIVE);
    }

    /// \copydoc IteratorAggregate::getIterator().
    public function getIterator()
    {
        return new RecursiveIteratorIterator(
            new RecursiveArrayIterator($this->_prefixes)
        );
    }

    /// \copydoc ArrayAccess::offsetGet().
    public function offsetGet($offset)
    {
        throw new Plop_Exception('Write-only collection');
    }

    /// \copydoc ArrayAccess::offsetSet().
    public function offsetSet($offset, $value)
    {
        if (!is_string($value)) {
            throw new Plop_Exception('A string was expected');
        }
        if (substr($value, -strlen(DIRECTORY_SEPARATOR)) !=
            DIRECTORY_SEPARATOR) {
            $value .= DIRECTORY_SEPARATOR;
        }
        $this->_prefixes[strlen($value)][] = $value;
    }

    /// \copydoc ArrayAccess::offsetExists().
    public function offsetExists($offset)
    {
        if (!is_string($offset)) {
            throw new Plop_Exception('A string was expected');
        }
        $len = strlen($offset);
        if (!isset($this->_prefixes[$len])) {
            return FALSE;
        }
        $key = array_search($this->_prefixes[$len], $offset, TRUE);
        return ($key !== FALSE);
    }

    /// \copydoc ArrayAccess::offsetUnset().
    public function offsetUnset($offset)
    {
        if (substr($offset, -strlen(DIRECTORY_SEPARATOR)) !=
            DIRECTORY_SEPARATOR) {
            $offset .= DIRECTORY_SEPARATOR;
        }
        $len = strlen($offset);
        $key = array_search($offset, $this->_prefixes[$len], TRUE);
        if ($key !== FALSE) {
            unset($this->_prefixes[$len][$key]);
            if (!count($this->_prefixes[$len])) {
                unset($this->_prefixes[$len]);
            }
        }
    }

    public function stripLongestPrefix($path)
    {
        foreach (array_reverse($this->_prefixes, TRUE) as $len => $prefixes) {
            foreach ($prefixes as $prefix) {
                if (!strncmp($path, $prefix, $len)) {
                    return (string) substr($path, $len);
                }
            }
        }
        return $path;
    }
}

