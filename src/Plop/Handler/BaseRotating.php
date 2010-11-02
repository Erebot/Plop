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

namespace PEAR2\Plop\Handler;
use \PEAR2\Plop\Handler,
    \PEAR2\Plop\Record;

abstract class  BaseRotating
extends         Handler
{
    public function emit(Record &$record)
    {
        try {
            if ($this->shouldRollover($record))
                $this->doRollover();
            parent::emit($record);
        }
        catch (\Exception $e) {
            $this->handleError($record, $e);
        }
    }
}

