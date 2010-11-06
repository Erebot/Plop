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

class Plop_Filterer
{
    public $filters;
    
    public function __construct()
    {
        $this->filters = array();
    }

    public function addFilter(Plop_Filter &$filter)
    {
        if (!in_array($filter, $this->filters, TRUE))
            $this->filters[] =& $filter;
    }

    public function removeFilter(Plop_Filter &$filter)
    {
        $key = array_search($filter, $this->filters, TRUE);
        if ($key !== FALSE)
            unset($this->filters[$key]);
    }

    public function filter(Plop_Record &$record)
    {
        $rv = 1;
        foreach ($this->filters as &$filter) {
            if (!$filter->filter($record)) {
                $rv = 0;
                break;
            }
        }
        unset($filter);
        return $rv;
    }
}

