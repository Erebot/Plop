<?php

require_once('PHPUnit/Framework.php');

use PEAR2\Plop;

class FileConfigTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Plop\Logger::$root     = new Plop\RootLogger(Plop\Plop::WARNING);
        Plop\Logger::$manager  = new Plop\Manager(Plop\Logger::$root);
        $this->logging =& Plop\Plop::getInstance();
    }

    protected function checkLoggingSettings()
    {
        $root = $this->logging->getLogger();
        $this->assertSame(Plop\Plop::DEBUG, $root->level);
        $this->assertSame(1, count($root->handlers));
        $this->assertTrue($root->handlers[0] instanceof Plop\Handler\Stream);
        $this->assertSame(Plop\Plop::ERROR, $root->handlers[0]->level);
    }

    public function testLoadXMLConfigurationFromFilename()
    {
        $this->logging->fileConfig(
            dirname(__FILE__).'/config.xml',
            array(),
            '\\PEAR2\\Plop\\Config\\Format\\XML'
        );
        $this->checkLoggingSettings();
    }

    public function testLoadINIConfigurationWithFilename()
    {
        $this->logging->fileConfig(
            dirname(__FILE__).'/config.ini',
            array(),
            '\\PEAR2\\Plop\\Config\\Format\\INI'
        );
        $this->checkLoggingSettings();
    }

    public function testLoadConfigurationWithFilename()
    {
        $this->logging->fileConfig(__DIR__.'/config.ini');
        $this->checkLoggingSettings();
    }
}

