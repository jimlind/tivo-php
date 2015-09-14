<?php

namespace JimLind\TiVo;

use JimLind\TiVo\Characteristic\LoggingTrait;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Service for decoding TiVo video files
 */
class VideoDecoder
{
    use LoggingTrait;

    /**
     * @var string
     */
    protected $mak;

    /**
     * @var ProcessBuilder
     */
    protected $builder;

    /**
     * @param string         $mak     TiVo's Media Access Key
     * @param ProcessBuilder $builder Symfony ProcessBuilder component
     */
    public function __construct($mak, ProcessBuilder $builder)
    {
        $this->mak     = $mak;
        $this->builder = $builder;

        $this->setNullLogger();
    }

    /**
     * Decode a TiVo file or log failure
     *
     * @param string $input  Where the encoded TiVo file is
     * @param string $output Where the decoded MPEG file goes
     *
     * @return boolean
     */
    public function decode($input, $output)
    {
        $process = $this->buildProcess($this->mak, $input, $output);
        $process->run();

        if ($process->isSuccessful() === false) {
            // Failure: Log and exit early
            $this->logger->warning('Problem executing command');
            $this->logger->warning('Details: `'.$process->getCommandLine().'`');

            return false;
        }

        return true;
    }

    /**
     * Build a Process to run tivodecode decoding a file with a MAK
     *
     * @param string $mak    TiVo's Media Access Key
     * @param string $input  Where the encoded TiVo file is
     * @param string $output Where the decoded MPEG file goes
     *
     * @return Process
     */
    protected function buildProcess($mak, $input, $output)
    {
        $this->builder->setPrefix('/usr/local/bin/tivodecode');
        $this->builder->setArguments([
            $input,
            '--mak='.$mak,
            '--no-verify',
            '--out='.$output,
        ]);
        $this->builder->setTimeout(null);

        return $this->builder->getProcess();
    }
}
