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
 *      A filter that only accepts records from a specific
 *      logger.
 */
class       Plop_Filter_Level
implements  Plop_FilterInterface
{
    /// Minimal log level that will be accepted (inclusive).
    protected $_level;

    /**
     * Construct a new instance of this filter.
     *
     * \param int|string $level
     *      Name or value of the minimal level that this filter
     *      should allow.
     */
    public function __construct($level)
    {
        if (is_string($level)) {
            $plop = Plop::getInstance();
            $level = Plop::getLevelValue($level);
        }
        if (!is_int($level)) {
            throw new Plop_Exception('Invalid value');
        }
        $this->_level = $level;
    }

    /// \copydoc Plop_FilterInterface::filter().
    public function filter(Plop_RecordInterface $record)
    {
        $res = $record['levelno'] >= $this->_level;
        return $res;
    }
}

