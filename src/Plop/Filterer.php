<?php

namespace PEAR2\Plop;

class Filterer
{
    public $filters;
    
    public function __construct()
    {
        $this->filters = array();
    }

    public function addFilter(Filter &$filter)
    {
        if (!in_array($filter, $this->filters, TRUE))
            $this->filters[] =& $filter;
    }

    public function removeFilter(Filter &$filter)
    {
        $key = array_search($filter, $this->filters, TRUE);
        if ($key !== FALSE)
            unset($this->filters[$key]);
    }

    public function filter(Record &$record)
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

