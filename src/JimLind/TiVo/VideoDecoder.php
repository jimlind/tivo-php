<?php

namespace JimLind\TiVo;

use Symfony\Component\Process\ProcessBuilder;

/**
 * Service for decoding encoded TiVo video files
 */
class VideoDecoder extends AbstractBase
{

    /**
     * @var string
     */
    protected $mak;

    /**
     * @var ProcessBuilder
     */
    protected $builder;

    /**
     * @param string         $mak     Your TiVo's Media Access Key
     * @param ProcessBuilder $builder The Symfony ProcessBuilder component
     */
    public function __construct($mak, ProcessBuilder $builder)
    {
        $this->mak     = $mak;
        $this->builder = $builder;

        parent::__construct();
    }

    /**
     * Decode a TiVo file to the new decoded file or log failure
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
            $message = 'Problem executing tivodecode. Tool may not be installed.';
            $this->logger->warning($message);
            $this->logger->warning('Command: '.$process->getCommandLine());

            return false;
        }

        return true;
    }

    /**
     * Builds the Process that calls TiVoDecode on the encoded file
     *
     * @param string $mak    TiVo Your TiVo's Media Access Key
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
