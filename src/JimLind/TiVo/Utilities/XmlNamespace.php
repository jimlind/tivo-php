<?php

namespace JimLind\TiVo\Utilities;

/**
 * Adjust XML Namespaces
 */
class XmlNamespace
{
    /**
     * Add the default namespace as 'tivo' namespace.
     *
     * @param SimpleXMLElement $simpleXml
     */
    public static function addTiVoNamespace(&$simpleXml)
    {
        if (!$simpleXml instanceof \SimpleXMLElement) {
            throw new \Exception('Input is not a SimpleXMLElement');
        }
        
        $namespaces = $simpleXml->getNamespaces(true);
        if (isset($namespaces[''])) {
            $namespaceUrl = $namespaces[''];
        } else {
            $namespaceUrl = 'http://www.w3.org/2001/XMLSchema';
            $pattern      = '/(?<!\?)(\/*>)/';
            $replace      = ' xmlns="' . $namespaceUrl . '"$0';
            $xmlString    = preg_replace($pattern, $replace, $simpleXml->asXml(), 1);
            $simpleXml    = simplexml_load_string($xmlString);
        }
        $simpleXml->registerXPathNamespace('tivo', $namespaceUrl);
    }
}
