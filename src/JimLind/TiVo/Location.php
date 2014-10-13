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
    protected $process = null;
    protected $logger = null;

    /**
     * Constructor
     *
     * @param \Symfony\Component\Process\Process $process The Symfony Process Component
     * @param \Psr\Log\LoggerInterface           $logger  A PSR-0 Logger
     */
    public function __construct(Process $process, LoggerInterface $logger = null)
    {
        $this->process = $process;
        $this->logger  = $logger;
    }

    /**
     * Attempt to find the TiVo and log any problems.
     *
     * Returns IP address or false
     *
     * @return boolean|string
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
            return $ipMatch;
        }

        Utilities\Log::warn('Unable to parse IP from Avahi.', $this->logger);
        // TiVo not found.
        return false;
    }

    /**
     * Execute the command line to run Avahi.
     *
     * @return string
     */
    protected function fetchAvahi()
    {
        // Command to find the records for the TiVo on TCP
        $command = 'avahi-browse -l -r -t _tivo-videos._tcp';

        $this->process->setCommandLine($command);
        $this->process->setTimeout(60); // 1 minute
        $this->process->run();

        return $this->process->getOutput();
    }

    /**
     * Regular Expression to find IP in Avahi output.
     *
     * @param string $avahiOutput
     *
     * @return boolean|string
     */
    protected function parseAvahi($avahiOutput)
    {
        $matches = array();
        $pattern = '/^\s+address = \[(\d+\.\d+\.\d+\.\d+)\]$/m';
        preg_match($pattern, $avahiOutput, $matches);
        if (!empty($matches) && isset($matches[1])) {
            return $matches[1];
        }

        return false;
    }

}
