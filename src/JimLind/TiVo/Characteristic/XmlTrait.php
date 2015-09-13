<?php

namespace JimLind\TiVo\Characteristic;

use Exception;
use SimpleXMLElement;

/**
 * Trait for handling XML
 */
trait XmlTrait
{
    /**
     * Register the default namespace as 'tivo' namespace
     *
     * @param SimpleXMLElement $simpleXml
     *
     * @return SimpleXMLElement
     */
    public function registerTiVoNamespace($simpleXml)
    {
        if (!$simpleXml instanceof SimpleXMLElement) {
            throw new Exception('Input is not a SimpleXMLElement');
        }

        $namespaces = $simpleXml->getNamespaces(true);
        if (false === isset($namespaces[''])) {
            $simpleXml = $this->addExampleSchema($simpleXml);
            $namespaces = $simpleXml->getNamespaces(true);
        }
        $namespaceUrl = $namespaces[''];
        $simpleXml->registerXPathNamespace('tivo', $namespaceUrl);

        return $simpleXml;
    }

    /**
     * Add the W3C example schema
     *
     * @param SimpleXMLElement $simpleXml
     *
     * @return SimpleXMLElement
     */
    protected function addExampleSchema($simpleXml)
    {
        $namespaceUrl = 'http://www.w3.org/2001/XMLSchema';
        $pattern      = '/(?<!\?)(\/*>)/';
        $replace      = ' xmlns="'.$namespaceUrl.'"$0';
        $xmlString    = preg_replace($pattern, $replace, $simpleXml->asXml(), 1);

        return simplexml_load_string($xmlString);
    }
}
