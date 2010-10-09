<?php

require_once('PHPUnit/Framework.php');
include_once(dirname(dirname(__FILE__)).'/src/logging.php');

/**
 * @runTestsInSeparateProcesses
 */
class FileConfigTest
extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
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
        $this->logging->fileConfig(dirname(__FILE__).'/config.xml', array(), 'XML');
        $this->checkLoggingSettings();
    }

#    public function testLoadXMLConfigurationWithSimpleXML()
#    {
#        $this->logging->fileConfig(dirname(__FILE__).'/config.xml',
#            array(), 'XML');
#        $this->checkLoggingSettings();
#    }

#    public function testLoadXMLConfigurationWithDOM()
#    {
#        $this->logging->fileConfig(dirname(__FILE__).'/config.xml',
#            array(), 'XML');
#        $this->checkLoggingSettings();
#    }

    public function testLoadINIConfigurationWithFilename()
    {
        $this->logging->fileConfig(dirname(__FILE__).'/config.ini', array(), 'INI');
        $this->checkLoggingSettings();
    }

    public function testLoadConfigurationWithFilename()
    {
        $this->logging->fileConfig(dirname(__FILE__).'/config.ini');
        $this->checkLoggingSettings();
    }

#    public function testLoadINIConfigurationWithArray()
#    {
#        $this->logging->fileConfig(dirname(__FILE__).'/config.ini',
#            array(), Plop::LOAD_XML);
#        $this->checkLoggingSettings();
#    }
}

?>