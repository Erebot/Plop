<?php
/*
    This file is part of Plop, a simple logging library for PHP.

    Copyright © 2010-2014 François Poirotte

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
    protected $callback;

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $params = stream_context_get_options($this->context);
        if (!isset($params['mock']['callback']) ||
            !is_callable($params['mock']['callback']) ||
            $mode != 'at') {
            return false;
        }

        $opened_path        =   $path;
        $this->callback     =&  $params['mock']['callback'];
        return true;
    }

    public function stream_close()
    {
        $params = stream_context_get_options($this->context);
        $params['stderr']['buffer'] =& $this->buffer;
        return true;
    }

    public function stream_flush()
    {
        return true;
    }

    public function stream_eof()
    {
        return false;
    }

    public function stream_tell()
    {
        return strlen($this->buffer);
    }

    public function stream_write($data)
    {
        call_user_func($this->callback, $data);
        return strlen($data);
    }
}
