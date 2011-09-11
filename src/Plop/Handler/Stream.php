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

class   Plop_Handler_Stream
extends Plop_Handler
{
    protected $_stream;

    public function __construct(&$stream = NULL)
    {
        parent::__construct();
        $this->_stream      =&  $stream;
        $this->formatter    =   NULL;
    }

    public function flush()
    {
        fflush($this->_stream);
    }

    public function emit(Plop_Record &$record)
    {
        if (!$this->_stream)
            $stream = fopen('php://stderr', 'at');
        else
            $stream =& $this->_stream;

        $msg = $this->format($record);
        fprintf($stream, "%s\n", $msg);

        if (!$this->_stream) {
            fflush($stream);
            fclose($stream);
        }
        else
            $this->flush();
    }
}

