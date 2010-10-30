<?php

require_once('PHPUnit/Framework.php');

class FileConfigTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        Plop_Logger::$root     = new Plop_RootLogger(PLOP_LEVEL_WARNING);
        Plop_Logger::$manager  = new Plop_Manager(PlopLogger::$root);
        $this->logging =& Plop::getInstance();
    }

    protected function checkLoggingSettings()
    {
        $root = $this->logging->getLogger();
        $this->assertSame(PLOP_LEVEL_DEBUG, $root->level);
        $this->assertSame(1, count($root->handlers));
        $this->assertTrue($root->handlers[0] instanceof PlopStreamHandler);
        $this->assertSame(PLOP_LEVEL_ERROR, $root->handlers[0]->level);
    }

    public function testLoadXMLConfigurationFromFilename()
    {
        $this->logging->fileConfig(__DIR__.'/config.xml', array(), 'XML');
        $this->checkLoggingSettings();
    }

    public function testLoadINIConfigurationWithFilename()
    {
        $this->logging->fileConfig(__DIR__.'/config.ini', array(), 'INI');
        $this->checkLoggingSettings();
    }

    public function testLoadConfigurationWithFilename()
    {
        $this->logging->fileConfig(__DIR__.'/config.ini');
        $this->checkLoggingSettings();
    }
}

?>
