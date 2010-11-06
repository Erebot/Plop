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

class FileConfigTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Plop_Logger::$root     = new Plop_RootLogger(Plop_Plop::WARNING);
        Plop_Logger::$manager  = new Plop_Manager(Plop_Logger::$root);
        $this->logging =& Plop_Plop::getInstance();
    }

    protected function checkLoggingSettings()
    {
        $root = $this->logging->getLogger();
        $this->assertSame(Plop_Plop::DEBUG, $root->level);
        $this->assertSame(1, count($root->handlers));
        $this->assertTrue($root->handlers[0] instanceof Plop_Handler_Stream);
        $this->assertSame(Plop_Plop::ERROR, $root->handlers[0]->level);
    }

    public function testLoadXMLConfigurationFromFilename()
    {
        $this->logging->fileConfig(
            dirname(__FILE__).'/config.xml',
            array(),
            'Plop_Config_Format_XML'
        );
        $this->checkLoggingSettings();
    }

    public function testLoadINIConfigurationWithFilename()
    {
        $this->logging->fileConfig(
            dirname(__FILE__).'/config.ini',
            array(),
            'Plop_Config_Format_INI'
        );
        $this->checkLoggingSettings();
    }

    public function testLoadConfigurationWithFilename()
    {
        $this->logging->fileConfig(__DIR__.'/config.ini');
        $this->checkLoggingSettings();
    }
}

