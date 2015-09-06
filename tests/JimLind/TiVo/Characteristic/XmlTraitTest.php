<?php

namespace JimLind\TiVo\Tests\Characteristic;

use JimLind\TiVo\Characteristic\XmlTrait;

/**
 * Test the TiVo\Utilities\Log class.
 */
class XmlNamespaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var XmlTrait Test Object
     */
    protected $fixture;

    protected function setUp()
    {
        $this->fixture = $this->getObjectForTrait('JimLind\TiVo\Characteristic\XmlTrait');
    }

    /**
     * Test a snippet of XML with a defined namespace.
     */
    public function testNamespacedXml()
    {
        $xml = '<items xmlns="http://www.example.org/schema"><item id="1" /></items>';
        $simpleXml = simplexml_load_string($xml);
        $outputXml = $this->fixture->registerTiVoNamespace($simpleXml);

        $itemList = $outputXml->xpath('tivo:item[@id = 1]');
        $this->assertCount(1, $itemList);

        $this->assertEquals(
            $outputXml->getDocNamespaces(true),
            $outputXml->getNamespaces(true)
        );
    }

    /**
     * Test a snippet of XML without a namespace.
     */
    public function testRawXml()
    {
        $xml = '<items><item id="1" /></items>';
        $simpleXml = simplexml_load_string($xml);
        $outputXml = $this->fixture->registerTiVoNamespace($simpleXml);

        $itemList = $outputXml->xpath('tivo:item[@id = 1]');
        $this->assertCount(1, $itemList);

        $this->assertEquals(
            $outputXml->getDocNamespaces(true),
            $outputXml->getNamespaces(true)
        );
    }

    /**
     * Test empty XML attempt.
     *
     * @expectedException Exception
     */
    public function testEmptyXMLString()
    {
        $xml = '';
        $simpleXml = simplexml_load_string($xml);
        $this->fixture->registerTiVoNamespace($simpleXml);
    }

    /**
     * Test XML element output.
     */
    public function testElementXMLString()
    {
        $xmlElement = simplexml_load_string('<a />');
        $outputXml  = $this->fixture->registerTiVoNamespace($xmlElement);

        $actual = $outputXml->asXml();

        $expected = '<?xml version="1.0"?>'."\n";
        $expected .= '<a xmlns="http://www.w3.org/2001/XMLSchema"/>'."\n";

        $this->assertEquals($expected, $actual);
    }

    /**
     * Test full XML structure output.
     */
    public function testStructureXMLString()
    {
        $xmlStructure = simplexml_load_string('<b><c /></b>');
        $outputXml    = $this->fixture->registerTiVoNamespace($xmlStructure);

        $actual = $outputXml->asXml();

        $expected = '<?xml version="1.0"?>'."\n";
        $expected .= '<b xmlns="http://www.w3.org/2001/XMLSchema"><c/></b>'."\n";

        $this->assertEquals($expected, $actual);
    }
}
