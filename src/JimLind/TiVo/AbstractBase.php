<?php

namespace JimLind\TiVo;

use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

abstract class AbstractBase
{
    use LoggerAwareTrait;

    public function __construct()
    {
        $this->logger = new NullLogger();
    }

}