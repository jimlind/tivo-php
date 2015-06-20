<?php

namespace JimLind\TiVo;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Decode is a service for decoding raw TiVo video files.
 */
class Decode
{
    /**
     * @var string
     */
    private $mak;

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
     * @param string         $mak     Your TiVo's Media Access Key.
     * @param ProcessBuilder $builder The Symfony ProcessBuilder component.
     */
    public function __construct($mak, ProcessBuilder $builder)
    {
        $this->mak     = $mak;
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
     * Decode a TiVo file and write the new file to the new location.
     *
     * @param string $input  Where the raw TiVo file is.
     * @param string $output Where the decode Mpeg file goes.
     *
     * @return boolean
     */
    public function decode($input, $output)
    {
        $process = $this->buildDecodeProcess($this->mak, $input, $output);
        $process->run();

        if ($process->isSuccessful() === false) {
            // Failure. Log and exit early.
            $message = 'Problem executing tivodecode. Tool may not be installed.';
            $this->logger->warning($message);
            $this->logger->warning('Command: ' . $process->getCommandLine());

            return false;
        }

        return true;
    }

    /**
     * Builds the SymfonyProcess.
     *
     * @param string $mak    TiVo key
     * @param string $input  Input file
     * @param string $output Output file
     * @return Process
     */
    protected function buildDecodeProcess($mak, $input, $output)
    {
        $this->builder->setPrefix('/usr/local/bin/tivodecode');
        $this->builder->setArguments([
            $input,
            '--mak=' . $mak,
            '--no-verify',
            '--out=' . $output,
        ]);
        $this->builder->setTimeout(null);

        return $this->builder->getProcess();
    }
}
