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

namespace Plop\Handler;

/**
 *  \brief
 *      An abstract class for handlers that must deal
 *      with file rotations.
 */
abstract class RotatingAbstract extends \Plop\Handler\File
{
    /// \copydoc Plop::HandlerAbstract::emit().
    protected function emit(\Plop\RecordInterface $record)
    {
        try {
            if ($this->shouldRollover($record)) {
                $this->doRollover();
            }
            parent::emit($record);
        } catch (\Exception $e) {
            $this->handleError($record, $e);
        }
    }

    /**
     * Decide whether a file rotation is necessary.
     *
     * \param Plop::RecordInterface $record
     *      The log record being handled.
     *
     * \retval bool
     *      \a true if a file rotation is required,
     *      \a false otherwise.
     */
    abstract protected function shouldRollover(\Plop\RecordInterface $record);

    /**
     * Do the actual file rotation.
     *
     * \return
     *      This method does not return any value.
     */
    abstract protected function doRollover();
}
