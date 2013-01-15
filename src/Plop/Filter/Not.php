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
 *      A filter that inverts the result of another filter.
 */
class       Plop_Filter_Not
implements  Plop_FilterInterface
{
    /// Subfilter to negate.
    protected $_subfilter;

    /**
     * Construct a new instance of this filter.
     *
     * \param Plop_FilterInterface $filter
     *      Subfilter whose filtering effect will be
     *      inverted by this filter instance.
     */
    public function __construct(Plop_FilterInterface $filter)
    {
        $this->_subfilter = $filter;
    }

    /// \copydoc Plop_FilterInterface::filter().
    public function filter(Plop_RecordInterface $record)
    {
        $res = !$this->_subfilter->filter($record);
        return $res;
    }
}

