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

require_once('PHPUnit/Framework.php');

use PEAR2\Plop;

class FileHandlerTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Plop\Logger::$root     = new Plop\RootLogger(Plop\Plop::WARNING);
        Plop\Logger::$manager  = new Plop\Manager(Plop\Logger::$root);
        $this->tempfile = tempnam(sys_get_temp_dir(), 'PLOP');
        $this->logging =& Plop\Plop::getInstance();
        $this->logging->basicConfig(array(
            'filename'  => $this->tempfile,
            'level'     => Plop\Plop::DEBUG,
        ));
    }

    public function tearDown()
    {
        $root = $this->logging->getLogger();
        $root->handlers = array();
        @unlink($this->tempfile);
    }

    public function testBasicFileHandler()
    {
        $message = 'This message should go to the log file';
        $this->logging->debug($message);
        $content = file_get_contents($this->tempfile);
        $expected = "DEBUG:root:$message\n";
        $this->assertEquals($expected, $content);
    }
}

