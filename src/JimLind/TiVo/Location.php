<?php

namespace JimLind\TiVo;

use Psr\Log;
use Symfony\Component\Process;

class Location
{
    protected $logger = null;

    function __construct(Log\LoggerInterface $logger = null) {
        if ($logger !== null) {
            $this->logger = $logger;
        }
    }

    public function find() {
        $avahiOutput = $this->fetchAvahi();

        if (empty($avahiOutput)) {
            $this->logger->addWarning('Problem locating a proper device on the
                network. The avahi-browse tool may not be installed.');
            return false;
        }

        $ipMatch = parseAvahiOutput($avahiOutput);
        if (empty($ipMatch)) {
            $this->logger->addWarning('Unable to parse IP from Avahi.');
            return false;
        } else {
            return $ipMatch;
        }

        $this->logger->addWarning('Unable to parse IP from Avahi.');
        return false;
    }
    
    protected function fetchAvahi() {
        $command = 'avahi-browse -l -r -t _tivo-videos._tcp';
        $process = new Process\Process($command);
        $process->setTimeout(60); // 1 minute
        $process->run();
        return $process->getOutput();
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
