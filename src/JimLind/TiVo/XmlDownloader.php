<?php

namespace JimLind\TiVo;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use JimLind\TiVo\Utilities\XmlNamespace;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Service for downloading list of shows on a TiVo.
 */
class XmlDownloader
{
    /**
     * @var Uri
     */
    private $uri;

    /**
     * @var string
     */
    private $mak;

    /**
     * @var ClientInterface
     */
    private $guzzle;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param string          $ip     The IP for the TiVo
     * @param string          $mak    The MAK for the TiVo
     * @param ClientInterface $guzzle A Guzzle Client
     */
    public function __construct($ip, $mak, ClientInterface $guzzle)
    {
        $originalUri = new Uri();
        $this->uri  = $originalUri
            ->withScheme('https')
            ->withHost($ip)
            ->withPath('TiVoConnect');

        $this->mak    = $mak;
        $this->guzzle = $guzzle;

        // Default to the NullLogger
        $this->setLogger(new NullLogger());
    }

    /**
     * Set the Logger
     *
     * @param Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Returns multiple XML file downloads merged into one array.
     *
     * @param SimpleXMLElement[] $previousShowList Array of previous shows
     *
     * @return SimpleXMLElement[]
     */
    public function download($previousShowList = [])
    {
        $xmlFile = $this->downloadXmlPiece(count($previousShowList));
        XmlNamespace::addTiVoNamespace($xmlFile);

        $showList = $xmlFile->xpath('//tivo:Item');
        if (count($showList) > 0) {
            // Recurse on next set of shows.
            $mergedShowList = array_merge($previousShowList, $showList);
            return $this->download($mergedShowList);
        } else {
            // Last set of shows reached.
            return $previousShowList;
        }
    }

    /**
     * Downloads a single piece of list as SimpleXML
     *
     * @param integer $anchorOffset Count of previous shows
     *
     * @return \SimpleXMLElement
     */
    private function downloadXmlPiece($anchorOffset)
    {
        $request = new Request('GET', $this->uri);
        $options = $this->buildOptions($anchorOffset);

        try {
            $response = $this->guzzle->send($request, $options);
        } catch (RequestException $exception) {
            $response = $this->parseException($exception);
        }

        return $this->parseResponse($response);
    }

    /**
     * Create an option array for Guzzle.
     *
     * @param integer $offset
     *
     * @return string[][]
     */
    private function buildOptions($offset)
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
     * Parse response from exception
     *
     * @param RequestException $exception
     *
     * @return Response
     */
    private function parseException(RequestException $exception)
    {
        if ($exception->hasResponse()) {
            return $exception->getResponse();
        } else {
            return new Response(000, [], $exception->getMessage());
        }
    }

    /**
     * Parse XML from the Guzzle Response
     *
     * @param ResponseInterface $response
     *
     * @return \SimpleXMLElement
     */
    private function parseResponse(ResponseInterface $response)
    {
        $responseCode = $response->getStatusCode();
        $responseBody = $response->getBody();

        if (200 !== $responseCode) {
            $this->logger->warning('Client response was not a success');
            $this->logger->warning($responseCode.': '.strip_tags($responseBody));
            return new \SimpleXMLElement('<xml />');
        }

        return $this->parseResponseBody($responseBody);
    }

    /**
     * Parse XML from the Guzzle Response Body
     *
     * @param string $responseBody
     * @return \SimpleXMLElement
     */
    private function parseResponseBody($responseBody)
    {
        try {
            libxml_use_internal_errors(true);

            return new \SimpleXMLElement($responseBody);
        } catch (\Exception $exception) {
            $this->logger->warning('Problem with SimpleXMLElement construction');
            $this->logger->warning($exception->getMessage());
        }

        return new \SimpleXMLElement('<xml />');
    }
}
