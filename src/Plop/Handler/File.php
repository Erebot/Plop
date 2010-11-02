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

class   File
extends Stream
{
    public $baseFilename;
    public $mode;

    public function __construct($filename, $mode='a', $encoding=NULL, $delay=0)
    {
        $this->baseFilename = $filename;
        $this->mode         = $mode;
        $this->encoding     = $encoding;
        if ($delay) {
            Handler::__construct();
            $this->_stream = FALSE;
        }
        else
            parent::__construct($this->open());
    }

    protected function open()
    {
        $stream = fopen($this->baseFilename, $this->mode);
        if (function_exists('stream_encoding') &&
            $this->encoding !== NULL && $stream !== FALSE)
            stream_encoding($stream, $this->encoding);
        return $stream;
    }

    public function close()
    {
        if ($this->_stream) {
            $this->flush();
            fclose($this->_stream);
            parent::close();
        }
    }
}

