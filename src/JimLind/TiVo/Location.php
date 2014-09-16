<?php

namespace JimLind\TiVo;

use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

class Location
{
    protected $process = null;
    protected $logger = null;

    function __construct(Process $process, LoggerInterface $logger) {
        $this->process = $process;
        $this->logger  = $logger;
    }

    public function find() {
        $avahiOutput = $this->fetchAvahi();

        if (empty($avahiOutput)) {
            $this->logger->warning(
                'Problem locating a proper device on the network. ' .
                'The avahi-browse tool may not be installed. '
            );
            return false;
        }

        $ipMatch = $this->parseAvahi($avahiOutput);
        if ($ipMatch) {
            return $ipMatch;
        }

        $this->logger->warning('Unable to parse IP from Avahi.');
        return false;
    }

    protected function fetchAvahi() {
        // Command to find the records for the TiVo on TCP
        $command = 'avahi-browse -l -r -t _tivo-videos._tcp';

        $this->process->setCommandLine($command);
        $this->process->setTimeout(60); // 1 minute
        $this->process->run();
        return $this->process->getOutput();
    }

    protected function parseAvahi($avahiOutput) {
        $matches = array();
        $pattern = '/^\s+address = \[(\d+\.\d+\.\d+\.\d+)\]$/m';
        preg_match($pattern, $avahiOutput, $matches);
        if (!empty($matches) && isset($matches[1])) {
            return $matches[1];
        }
        return false;
    }
}
