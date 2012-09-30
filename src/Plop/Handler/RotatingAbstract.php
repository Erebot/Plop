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
 *      An abstract class for handlers that must deal
 *      with file rotations.
 */
abstract class  Plop_Handler_RotatingAbstract
extends         Plop_Handler_File
{
    /// \copydoc Plop_HandlerAbstract::_emit().
    protected function _emit(Plop_RecordInterface $record)
    {
        try {
            if ($this->_shouldRollover($record)) {
                $this->_doRollover();
            }
            parent::_emit($record);
        }
        catch (Exception $e) {
            $this->handleError($record, $e);
        }
    }

    /**
     * Decide whether a file rotation is necessary.
     *
     * \param Plop_RecordInterface $record
     *      The log record being handled.
     *
     * \retval bool
     *      \a TRUE if a file rotation is required,
     *      \a FALSE otherwise.
     */
    abstract protected function _shouldRollover(Plop_RecordInterface $record);

    /**
     * Do the actual file rotation.
     *
     * \return
     *      This method does not return any value.
     */
    abstract protected function _doRollover();
}

