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

class       Plop_Filterer
implements  Plop_FiltererInterface
{
    protected $_filters;
    
    public function __construct()
    {
        $this->_filters = array();
    }

    public function addFilter(Plop_FilterInterface $filter)
    {
        if (!in_array($filter, $this->_filters, TRUE))
            $this->_filters[] = $filter;
    }

    public function removeFilter(Plop_FilterInterface $filter)
    {
        $key = array_search($filter, $this->_filters, TRUE);
        if ($key !== FALSE)
            unset($this->_filters[$key]);
    }

    public function getFilters()
    {
        return $this->_filters;
    }

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

