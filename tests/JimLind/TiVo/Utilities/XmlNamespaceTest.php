<?php

namespace JimLind\TiVo\Tests\Utilities;

use JimLind\TiVo\Extra\XmlTrait;

/**
 * Test the TiVo\Utilities\Log class.
 */
class XmlNamespaceTest extends \PHPUnit_Framework_TestCase
{
    use XmlTrait;

    /**
     * Test a snippet of XML with a defined namespace.
     */
    public function testNamespacedXml()
    {
        $xml = '<items xmlns="http://www.example.org/schema"><item id="1" /></items>';
        $simpleXml = simplexml_load_string($xml);
        $outputXml = $this->addTiVoNamespace($simpleXml);

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
        $outputXml = $this->addTiVoNamespace($simpleXml);

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
        $this->addTiVoNamespace($simpleXml);
    }

    /**
     * Test XML element output.
     */
    public function testElementXMLString()
    {
        $xml = '<item/>';
        $simpleXml = simplexml_load_string($xml);
        $outputXml = $this->addTiVoNamespace($simpleXml);

        $xmlString = $outputXml->asXml();

        $expected = '<?xml version="1.0"?>'."\n";
        $expected .= '<item xmlns="http://www.w3.org/2001/XMLSchema"/>'."\n";

        $this->assertEquals($expected, $xmlString);
    }

    /**
     * Test full XML structure output.
     */
    public function testStructureXMLString()
    {
        $xml = '<items><item /></items>';
        $simpleXml = simplexml_load_string($xml);
        $outputXml = $this->addTiVoNamespace($simpleXml);

        $xmlString = $outputXml->asXml();

        $expected = '<?xml version="1.0"?>'."\n";
        $expected .= '<items xmlns="http://www.w3.org/2001/XMLSchema"><item/></items>'."\n";

        $this->assertEquals($expected, $xmlString);
    }
}
