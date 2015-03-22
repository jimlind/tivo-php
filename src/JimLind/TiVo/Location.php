<?php

namespace JimLind\TiVo;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
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
     */
    public function __construct(Process $process)
    {
        $this->process = $process;

        // Default to the NullLogger
        $this->setLogger(new NullLogger());
    }

    /**
     * Set the Logger
     *
     * @param Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger) {
        $this->logger = $logger;
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
            $message = 'Problem locating a proper device on the network. ' .
                       'The avahi-browse tool may not be installed. ';
            $this->logger->emergency($message);
            // Exit early.
            return false;
        }

        $ipMatch = $this->parseAvahi($avahiOutput);
        if ($ipMatch) {
            // IP successfully parsed.
            return $ipMatch;
        }

        $message = 'Unable to parse IP from Avahi.';
        $this->logger->emergency($message);

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
