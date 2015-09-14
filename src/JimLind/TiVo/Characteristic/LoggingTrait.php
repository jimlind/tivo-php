<?php

namespace JimLind\TiVo\Characteristic;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * Trait for handling Logging
 */
trait LoggingTrait
{
    use LoggerAwareTrait;

    /**
     * Set the PSR logger to null
     */
    public function setNullLogger()
    {
        $this->setLogger(new NullLogger());
    }
}
