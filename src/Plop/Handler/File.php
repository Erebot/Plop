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

class   Plop_Handler_File
extends Plop_Handler_Stream
{
    protected $_baseFilename;
    protected $_mode;

    public function __construct($filename, $mode='at', $encoding=NULL, $delay=0)
    {
        $this->_baseFilename    = $filename;
        $this->_mode            = $mode;
        $this->_encoding        = $encoding;
        if ($delay) {
            Plop_Handler::__construct();
            $this->_stream = FALSE;
        }
        else {
            $stream = $this->_open();
            parent::__construct($stream);
        }
    }

    public function __destruct()
    {
        $this->_close();
    }

    protected function _open()
    {
        $stream = fopen($this->_baseFilename, $this->_mode);
        if (function_exists('stream_encoding') &&
            $this->_encoding !== NULL &&
            $stream !== FALSE)
            stream_encoding($stream, $this->_encoding);
        return $stream;
    }

    protected function _close()
    {
        if ($this->_stream) {
            $this->_flush();
            fclose($this->_stream);
        }
    }
}

