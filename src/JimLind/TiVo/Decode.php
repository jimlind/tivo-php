<?php

namespace JimLind\TiVo;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Process\Process;

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
     * @var Process
     */
    protected $process = null;

    /**
     * @var LoggerInterface
     */
    protected $logger  = null;

    /**
     * Constructor
     *
     * @param string  $mak     Your TiVo's Media Access Key.
     * @param Process $process The Symfony Process Component.
     */
    public function __construct($mak, Process $process)
    {
        $this->mak     = $mak;
        $this->process = $process;

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
        $command = 'tivodecode ' . $input . ' -m ' . $this->mak . ' -n -o ' . $output;

        $this->process->setCommandLine($command);
        $this->process->setTimeout(0); // Remove timeout.
        $this->process->run();

        if ($this->process->isSuccessful() === false) {
            // Failure. Log and exit early.
            $message = 'Problem executing tivodecode. Tool may not be installed.';
            $this->logger->warning($message);
            $this->logger->warning($command);

            return false;
        }

        return true;
    }
}
