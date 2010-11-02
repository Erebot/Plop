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

class   Stream
extends Handler
{
    protected $_stream;

    public function __construct(&$stream = NULL)
    {
        parent::__construct();
        if ($stream === NULL)
            $stream = fopen('php://stderr', 'at');
        $this->_stream       =&  $stream;
        $this->formatter    =   NULL;
    }

    public function flush()
    {
        fflush($this->_stream);
    }

    public function emit(Record &$record)
    {
        $msg = $this->format($record);
        fprintf($this->_stream, "%s\n", $msg);
        $this->flush();
    }
}

