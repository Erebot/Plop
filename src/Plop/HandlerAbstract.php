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

abstract class  Plop_HandlerAbstract
extends         Plop_Filterer
implements      Plop_HandlerInterface
{
    protected $_level;
    protected $_formatter;

    public function __construct(Plop_FormatterInterface $formatter = NULL)
    {
        if ($formatter === NULL) {
            $formatter = new Plop_Formatter();
        }
        parent::__construct();
        $this->setLevel(Plop::NOTSET);
        $this->setFormatter($formatter);
    }

    public function getLevel()
    {
        return $this->_level;
    }

    public function setLevel($level)
    {
        $this->_level = $level;
        return $this;
    }

    abstract protected function _emit(Plop_RecordInterface $record);

    protected function _format(Plop_RecordInterface $record)
    {
        return $this->_formatter->format($record);
    }

    public function handle(Plop_RecordInterface $record)
    {
        $rv = $this->_format($record);
        if ($rv) {
            $this->_emit($record);
        }
        return $rv;
    }

    public function getFormatter()
    {
        return $this->_formatter;
    }

    public function setFormatter(Plop_FormatterInterface $formatter)
    {
        $this->_formatter = $formatter;
        return $this;
    }

    public function handleError(
        Plop_RecordInterface    $record,
        Exception               $exception
    )
    {
        $stderr = fopen('php://stderr', 'at');
        fprintf($stderr, "%s\n", $exception);
        fclose($stderr);
        return $this;
    }
}

