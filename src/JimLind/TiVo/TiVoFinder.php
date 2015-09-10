<?php

namespace JimLind\TiVo;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Service for finding TiVo on your local network
 */
class TiVoFinder extends AbstractBase
{

    /**
     * @var ProcessBuilder
     */
    protected $builder;

    /**
     * @param ProcessBuilder $builder Symfony ProcessBuilder component
     */
    public function __construct(ProcessBuilder $builder)
    {
        $this->builder = $builder;

        parent::__construct();
    }

    /**
     * Find TiVo or log failure
     *
     * @return string
     */
    public function find()
    {
        $output = $this->getProcessOutput();

        if (empty($output)) {
            // Failure: Log and exit early
            $this->logger->warning('Unable to locate a TiVo device on the network');

            return '';
        }

        return $this->parseOutput($output);
    }

    /**
     * Run a Process and get results or log failure
     *
     * @return string
     */
    protected function getProcessOutput()
    {
        $process = $this->buildProcess();
        $process->run();

        if ($process->isSuccessful() === false) {
            // Failure: Log and exit early
            $this->logger->warning('Problem executing command');
            $this->logger->warning('Details: `'.$process->getCommandLine().'`');

            return '';
        }

        // Entirety of standard output of the command
        return $process->getOutput();
    }

    /**
     * Build a Process to run avahi-browse looking for TiVo on TCP
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
     * Parse IP from output or log failure
     *
     * @param string $output Output of Process
     *
     * @return string
     */
    protected function parseOutput($output)
    {
        $matches = [];
        $pattern = '/^\s+address = \[(\d+\.\d+\.\d+\.\d+)\]$/m';
        preg_match($pattern, $output, $matches);

        if (empty($matches) || count($matches) < 2) {
            // Failure: Log and exit early
            $this->logger->warning('Unable to parse IP');
            $this->logger->warning('Input: `'.$output.'`');

            return '';
        }

        return $matches[1];
    }
}
