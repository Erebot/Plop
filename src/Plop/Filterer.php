<?php

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

