<?php

namespace JimLind\TiVo;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Service for finding a TiVo on your local network.
 */
class TiVoFinder
{
    /**
     * @var ProcessBuilder
     */
    protected $builder = null;

    /**
     * @var LoggerInterface
     */
    protected $logger  = null;

    /**
     * Constructor
     *
     * @param ProcessBuilder $builder The Symfony ProcessBuilder component.
     */
    public function __construct(ProcessBuilder $builder)
    {
        $this->builder = $builder;

        // Default to the NullLogger
        $this->setLogger(new NullLogger());
    }

    /**
     * Set the Logger
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Attempt to find the TiVo and log any problems.
     *
     * @return string
     */
    public function find()
    {
        $avahiResults = $this->getAvahiResults();

        if (empty($avahiResults)) {
            // Failure. Log and exit early.
            $message = 'Unable to locate a TiVo device on the network.';
            $this->logger->warning($message);

            return '';
        }

        return $this->parseAvahiResults($avahiResults);
    }

    /**
     * Get string output from Avahi attempting to locate a TiVo.
     *
     * @return string
     */
    protected function getAvahiResults()
    {
        $process = $this->buildLocationProcess();
        $process->run();

        if ($process->isSuccessful() === false) {
            // Failure. Log and exit early.
            $message = 'Problem executing avahi-browse. Tool may not be installed.';
            $this->logger->warning($message);
            $this->logger->warning('Command: '.$process->getCommandLine());

            return '';
        }

        // Command line output.
        return $process->getOutput();
    }

    /**
     * Builds the SymfonyProcess.
     *
     * @return Process
     */
    protected function buildLocationProcess()
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
     * Regular expression to find IP in Avahi Output.
     *
     * @param string $avahiResult Output of the call to Avahi
     *
     * @return string
     */
    protected function parseAvahiResults($avahiResult)
    {
        $matches = [];
        $pattern = '/^\s+address = \[(\d+\.\d+\.\d+\.\d+)\]$/m';
        preg_match($pattern, $avahiResult, $matches);

        if (empty($matches) || count($matches) < 2) {
            // Failure. Log and exit early.
            $message = 'Unable to parse IP from Avahi output.';
            $this->logger->warning($message);

            return '';
        }

        return $matches[1];
    }
}
