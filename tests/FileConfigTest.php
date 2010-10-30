<?php

require_once('PHPUnit/Framework.php');
require_once(dirname(__FILE__).'/../src/Plop/Plop.php');

class FileConfigTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Plop_Logger::$root     = new Plop_RootLogger(PLOP_LEVEL_WARNING);
        Plop_Logger::$manager  = new Plop_Manager(Plop_Logger::$root);
        $this->logging =& Plop::getInstance();
    }

    protected function checkLoggingSettings()
    {
        $root = $this->logging->getLogger();
        $this->assertSame(PLOP_LEVEL_DEBUG, $root->level);
        $this->assertSame(1, count($root->handlers));
        $this->assertTrue($root->handlers[0] instanceof Plop_Handler_Stream);
        $this->assertSame(PLOP_LEVEL_ERROR, $root->handlers[0]->level);
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

