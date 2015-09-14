<?php

namespace JimLind\TiVo;

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use JimLind\TiVo\Characteristic\LoggingTrait;
use JimLind\TiVo\Characteristic\XmlTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SimpleXMLElement;

/**
 * Service for downloading a list of shows from TiVo
 */
class XmlDownloader
{
    use LoggingTrait;
    use XmlTrait;

    /**
     * @var Uri
     */
    protected $uri;

    /**
     * @var string
     */
    protected $mak;

    /**
     * @var ClientInterface
     */
    protected $guzzle;

    /**
     * @param string          $ip     TiVo's IP Address
     * @param string          $mak    TiVo's Media Access Key
     * @param ClientInterface $guzzle Guzzle Client
     */
    public function __construct($ip, $mak, ClientInterface $guzzle)
    {
        $this->uri = (new Uri())
            ->withScheme('https')
            ->withHost($ip)
            ->withPath('TiVoConnect');

        $this->mak    = $mak;
        $this->guzzle = $guzzle;

        $this->setNullLogger();
    }

    /**
     * Get a list of shows as XMLElements recursively building on previous downloads
     *
     * @param SimpleXMLElement[] $previousShowList List of shows already downloaded
     *
     * @return SimpleXMLElement[]
     */
    public function download($previousShowList = [])
    {
        $rawXml        = $this->downloadXmlPiece(count($previousShowList));
        $namespacedXml = $this->registerTiVoNamespace($rawXml);

        $showList = $namespacedXml->xpath('//tivo:Item');
        if (count($showList) > 0) {
            $mergedShowList = array_merge($previousShowList, $showList);
            // Recurse on next set of shows
            return $this->download($mergedShowList);
        } else {
            // Last set of shows reached
            return $previousShowList;
        }
    }

    /**
     * Downloads a single piece of list as SimpleXML
     *
     * @param integer $anchorOffset Count of previous shows
     *
     * @return SimpleXMLElement
     */
    private function downloadXmlPiece($anchorOffset)
    {
        $request  = new Request('GET', $this->uri);
        $options  = $this->buildGuzzleOptions($anchorOffset);
        $response = $this->getResponse($this->guzzle, $request, $options);

        return $this->parseResponse($response);
    }

    /**
     * Create an option array for Guzzle
     *
     * @param integer $offset
     *
     * @return string[][]
     */
    private function buildGuzzleOptions($offset)
    {
        return [
            'auth'  => ['tivo', $this->mak, 'digest'],
            'query' => [
                'Command'      => 'QueryContainer',
                'Container'    => '/NowPlaying',
                'Recurse'      => 'Yes',
                'AnchorOffset' => $offset,
            ],
            'verify' => false,
        ];
    }

    /**
     * Always get a response from a request by catching all exceptions
     *
     * @param ClientInterface $client
     * @param RequestInterface $request
     * @param mixed[] $options
     *
     * @return ResponseInterface
     */
    private function getResponse(ClientInterface $client, RequestInterface $request, $options)
    {
        try {
            $response = $client->send($request, $options);
        } catch (BadResponseException $requestException) {
            $response = $requestException->getResponse();
        } catch (Exception $exception) {
            $response = new Response(0, [], $exception->getMessage());
        }

        return $response;
    }

    /**
     * Parse XML from the Guzzle Response
     *
     * If response is not a success, log it and return an empty XML object
     *
     * @param ResponseInterface $response
     *
     * @return SimpleXMLElement
     */
    private function parseResponse(ResponseInterface $response)
    {
        $responseCode = $response->getStatusCode();
        $responseBody = $response->getBody();

        if (200 !== $responseCode) {
            $this->logger->warning('Client response was not a success');
            $this->logger->warning($responseCode.': `'.strip_tags($responseBody).'`');

            return new SimpleXMLElement('<xml />');
        }

        return $this->parseResponseXml($responseBody);
    }

    /**
     * Parse XML from the Guzzle Response Body
     *
     * If parsing XML parsing errors, log it and return an empty XML object
     *
     * @param string $responseBody
     *
     * @return SimpleXMLElement
     */
    private function parseResponseXml($responseBody)
    {
        try {
            libxml_use_internal_errors(true);

            return new SimpleXMLElement($responseBody);
        } catch (Exception $exception) {
            $this->logger->warning('Problem with SimpleXMLElement construction');
            $this->logger->warning('Message: `'.$exception->getMessage().'`');
        }

        return new SimpleXMLElement('<xml />');
    }
}
