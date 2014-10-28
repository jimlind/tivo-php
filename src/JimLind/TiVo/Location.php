<?php

namespace JimLind\TiVo;

use JimLind\TiVo\Utilities;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

/**
 * Location is a service for finding a TiVo on your local network.
 */
class Location
{
    /**
     * @var Symfony\Component\Process\Process
     */
    protected $process = null;

    /**
     * @var Psr\Log\LoggerInterface
     */
    protected $logger  = null;

    /**
     * Constructor
     *
     * @param Symfony\Component\Process\Process $process The Symfony Process Component.
     * @param Psr\Log\LoggerInterface           $logger  A PSR-0 Logger.
     */
    public function __construct(Process $process, LoggerInterface $logger = null)
    {
        $this->process = $process;
        $this->logger  = $logger;
    }

    /**
     * Attempt to find the TiVo and log any problems.
     *
     * @return string|boolean
     */
    public function find()
    {
        $avahiOutput = $this->fetchAvahi();

        if (empty($avahiOutput)) {
            $warning = 'Problem locating a proper device on the network. ' .
                       'The avahi-browse tool may not be installed. ';
            Utilities\Log::warn($warning, $this->logger);
            // Exit early.
            return false;
        }

        $ipMatch = $this->parseAvahi($avahiOutput);
        if ($ipMatch) {
            // IP successfully parsed.
            return $ipMatch;
        }

        Utilities\Log::warn('Unable to parse IP from Avahi.', $this->logger);
        // TiVo not found.
        return false;
    }

    /**
     * Get string output from Avahi attempting to locate TiVo.
     *
     * @return string
     */
    protected function fetchAvahi()
    {
        $command = 'avahi-browse -l -r -t _tivo-videos._tcp';

        $this->process->setCommandLine($command);
        $this->process->setTimeout(60); // 1 minute
        $this->process->run();
        // Command line output.
        return $this->process->getOutput();
    }

    /**
     * Regular expression to find IP in Avahi output.
     *
     * @param string $avahiOutput Output of the call to Avahi
     *
     * @return string|boolean
     */
    protected function parseAvahi($avahiOutput)
    {
        $matches = array();
        $pattern = '/^\s+address = \[(\d+\.\d+\.\d+\.\d+)\]$/m';
        preg_match($pattern, $avahiOutput, $matches);
        if (!empty($matches) && isset($matches[1])) {
            // Successfully parsed.
            return $matches[1];
        }
        // Nothing parsed.
        return false;
    }

}
