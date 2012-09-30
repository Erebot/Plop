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
class       Plop_Filter_Name
implements  Plop_FilterInterface
{
    /// Identifier of the logger whose records will be handled.
    protected $_name;

    /// Length of the identifier.
    protected $_nlen;

    /**
     * Construct a new instance of this filter.
     *
     * \param string $name
     *      (optional) Identifier of the only logger
     *      this filter will accept records from.
     *      An empty string means "accept any logger".
     *      By default, an empty string is used.
     */
    public function __construct($name = '')
    {
        $this->_name = $name;
        $this->_nlen = strlen($name);
    }

    /// \copydoc Plop_FilterInterface::filter().
    public function filter(Plop_RecordInterface $record)
    {
        if (!$this->_nlen)
            return TRUE;

        list(, , $name) = explode($record['name'], 3);
        if (strncmp($name, $this->_name, $this->_nlen))
            return FALSE;

        return (
            strlen($name) == $this->_nlen ||
            $name[$this->_nlen] == DIRECTORY_SEPARATOR
        );
    }
}

