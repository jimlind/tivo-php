<?php

namespace Tests\JimLind\TiVo\Model;

use JimLind\TiVo\Utilities;

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
        Utilities\XmlNamespace::addTiVoNamespace($simpleXml);

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
        Utilities\XmlNamespace::addTiVoNamespace($simpleXml);

        $itemList = $simpleXml->xpath('tivo:item[@id = 1]');
        $this->assertCount(1, $itemList);

        $this->assertEquals(
            $simpleXml->getDocNamespaces(true),
            $simpleXml->getNamespaces(true)
        );
    }
}

