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

class Plop_Testenv_Socket
{
    public $context;
    protected $mock;

    final public function stream_open($path, $mode, $options, &$opened_path)
    {
        $params = stream_context_get_options($this->context);
        if (!isset($params['mock']['object']) ||
            !is_object($params['mock']['object']) ||
            $mode != 'a+t') {
            return false;
        }

        $opened_path    =   $path;
        $this->mock     =&  $params['mock']['object'];
        return true;
    }

    public function stream_close()
    {
        return call_user_func(array($this->mock, 'stream_close'));
    }
}
