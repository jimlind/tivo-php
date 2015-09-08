<?php

namespace JimLind\TiVo;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Base class for setting up logger methods and default logger
 */
abstract class AbstractBase
{
    use LoggerAwareTrait;

    /**
     * No parameters neccessary
     */
    public function __construct()
    {
        $this->logger = new NullLogger();
    }
}
