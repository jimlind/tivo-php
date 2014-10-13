<?php

namespace JimLind\TiVo\Utilities;

/**
 * Logger Helper Util
 */
class Log
{
    /**
     * Logs a warning if a logger is available.
     *
     * @param string                   $warning
     * @param \Psr\Log\LoggerInterface $logger A PSR-0 Logger
     */
    public static function warn($warning, $logger)
    {
        if ($logger) {
            $logger->warning($warning);
        }
    }
}