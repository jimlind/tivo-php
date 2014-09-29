<?php

include 'vendor/autoload.php';

$process = new Symfony\Component\Process\Process('');

class Logger implements Psr\Log\LoggerInterface
{
    public function error($x, array $y = Array()){}
    public function debug($x, array $y = Array()){}
    public function critical($x, array $y = Array()){}
    public function warning($x, array $y = Array()){}
    public function notice($x, array $y = Array()){}
    public function emergency($x, array $y = Array()){}
    public function log($l, $x, array $y = Array()){}
    public function alert($x, array $y = Array()){}
    public function info($x, array $y = Array()){}
}

$location = new JimLind\TiVo\Location($process, new Logger());
var_dump($location->find());