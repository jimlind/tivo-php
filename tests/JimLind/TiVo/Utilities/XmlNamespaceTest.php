<?php

namespace JimLind\TiVo\Tests\Utilities;

use JimLind\TiVo\Utilities\XmlNamespace;

/**
 * Test the TiVo\Utilities\Log class.
 */
class XmlNamespaceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test a snippet of XML with a defined namespace.
     */
    public function testNamespacedXml()
    {
        $xml = '<items xmlns="http://www.example.org/schema"><item id="1" /></items>';
        $simpleXml = simplexml_load_string($xml);
        XmlNamespace::addTiVoNamespace($simpleXml);

        $itemList = $simpleXml->xpath('tivo:item[@id = 1]');
        $this->assertCount(1, $itemList);

        $this->assertEquals(
            $simpleXml->getDocNamespaces(true),
            $simpleXml->getNamespaces(true)
        );
    }

    /**
     * Test a snippet of XML without a namespace.
     */
    public function testRawXml()
    {
        $xml = '<items><item id="1" /></items>';
        $simpleXml = simplexml_load_string($xml);
        XmlNamespace::addTiVoNamespace($simpleXml);

        $itemList = $simpleXml->xpath('tivo:item[@id = 1]');
        $this->assertCount(1, $itemList);

        $this->assertEquals(
            $simpleXml->getDocNamespaces(true),
            $simpleXml->getNamespaces(true)
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
        XmlNamespace::addTiVoNamespace($simpleXml);
    }

    /**
     * Test XML element output.
     */
    public function testElementXMLString()
    {
        $xml = '<item/>';
        $simpleXml = simplexml_load_string($xml);
        XmlNamespace::addTiVoNamespace($simpleXml);

        $xmlString = $simpleXml->asXml();

        $expected = '<?xml version="1.0"?>' . "\n"
                  . '<item xmlns="http://www.w3.org/2001/XMLSchema"/>' . "\n" ;

        $this->assertEquals($expected, $xmlString);
    }

    /**
     * Test full XML structure output.
     */
    public function testStructureXMLString()
    {
        $xml = '<items><item /></items>';
        $simpleXml = simplexml_load_string($xml);
        XmlNamespace::addTiVoNamespace($simpleXml);

        $xmlString = $simpleXml->asXml();

        $expected = '<?xml version="1.0"?>' . "\n"
                  . '<items xmlns="http://www.w3.org/2001/XMLSchema"><item/></items>' . "\n" ;

        $this->assertEquals($expected, $xmlString);
    }
}
