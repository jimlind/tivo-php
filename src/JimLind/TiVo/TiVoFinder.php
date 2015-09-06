<?php

namespace JimLind\TiVo;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Service for finding a TiVo on your local network
 */
class TiVoFinder
{
    /**
     * @var ProcessBuilder
     */
    protected $builder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param ProcessBuilder $builder The Symfony ProcessBuilder component
     */
    public function __construct(ProcessBuilder $builder)
    {
        $this->builder = $builder;

        // Default to the NullLogger
        $this->setLogger(new NullLogger());
    }

    /**
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Find a TiVo or log failure
     *
     * @return string
     */
    public function find()
    {
        $avahiResults = $this->getProcessResults();

        if (empty($avahiResults)) {
            // Failure: Log and exit early
            $message = 'Unable to locate a TiVo device on the network.';
            $this->logger->warning($message);

            return '';
        }

        return $this->parseResults($avahiResults);
    }

    /**
     * Run the Process to find a TiVo get results or log failure
     *
     * @return string
     */
    protected function getProcessResults()
    {
        $process = $this->buildProcess();
        $process->run();

        if ($process->isSuccessful() === false) {
            // Failure: Log and exit early
            $message = 'Problem executing avahi-browse. Tool may not be installed.';
            $this->logger->warning($message);
            $this->logger->warning('Command: '.$process->getCommandLine());

            return '';
        }

        // Entirety of standard output of the command
        return $process->getOutput();
    }

    /**
     * Builds the Process that calls Avahi looking for a TiVo
     *
     * @return Process
     */
    protected function buildProcess()
    {
        $this->builder->setPrefix('avahi-browse');
        $this->builder->setArguments([
            '--ignore-local',
            '--resolve',
            '--terminate',
            '_tivo-videos._tcp',
        ]);
        $this->builder->setTimeout(60);

        return $this->builder->getProcess();
    }

    /**
     * Parse IP from Process result or log failure
     *
     * @param string $avahiResult Output of Process calling Avahi
     *
     * @return string
     */
    protected function parseResults($avahiResult)
    {
        $matches = [];
        $pattern = '/^\s+address = \[(\d+\.\d+\.\d+\.\d+)\]$/m';
        preg_match($pattern, $avahiResult, $matches);

        if (empty($matches) || count($matches) < 2) {
            // Failure: Log and exit early
            $message = 'Unable to parse IP from Avahi output.';
            $this->logger->warning($message);
            $this->logger->warning('Output: "'.$avahiResult.'"');

            return '';
        }

        return $matches[1];
    }
}
