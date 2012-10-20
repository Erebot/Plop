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
class       Plop_Filterer
implements  Plop_FiltererInterface
{
    /// List of active filters.
    protected $_filters;

    /// Construct a new instance capable of filtering records.
    protected function __construct()
    {
        $this->_filters = array();
    }

    /// \copydoc Plop_FiltererInterface::addFilter().
    public function addFilter(Plop_FilterInterface $filter)
    {
        if (!in_array($filter, $this->_filters, TRUE))
            $this->_filters[] = $filter;
        return $this;
    }

    /// \copydoc Plop_FiltererInterface::removeFilter().
    public function removeFilter(Plop_FilterInterface $filter)
    {
        $key = array_search($filter, $this->_filters, TRUE);
        if ($key !== FALSE)
            unset($this->_filters[$key]);
        return $this;
    }

    /// \copydoc Plop_FiltererInterface::getFilters().
    public function getFilters()
    {
        return $this->_filters;
    }

    /// \copydoc Plop_FiltererInterface::filter().
    public function filter(Plop_RecordInterface $record)
    {
        foreach ($this->_filters as $filter) {
            if (!$filter->filter($record)) {
                return FALSE;
            }
        }
        return TRUE;
    }
}

