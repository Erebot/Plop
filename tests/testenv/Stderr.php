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

class Plop_Testenv_Stderr
{
    public $context;
    protected $_callback;

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $params = stream_context_get_options($this->context);
        if (!isset($params['mock']['callback']) ||
            !is_callable($params['mock']['callback']) ||
            $mode != 'at') {
            return FALSE;
        }

        $opened_path        =   $path;
        $this->_callback    =&  $params['mock']['callback'];
        return TRUE;
    }

    public function stream_close()
    {
        $params = stream_context_get_options($this->context);
        $params['stderr']['buffer'] =& $this->_buffer;
        return TRUE;
    }

    public function stream_flush()
    {
        return TRUE;
    }

    public function stream_eof()
    {
        return FALSE;
    }

    public function stream_tell()
    {
        return strlen($this->_buffer);
    }

    public function stream_write($data)
    {
        call_user_func($this->_callback, $data);
        return strlen($data);
    }
}
