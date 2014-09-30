<?php

include 'vendor/autoload.php';

$process = new Symfony\Component\Process\Process('');
$guzzle = new GuzzleHttp\Client();

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
$ipAddress = $location->find();

$ip  = '192.168.0.1';
$mak = '7678999999';
$nowPlaying = new JimLind\TiVo\NowPlaying($ip, $mak, $guzzle, new Logger());
$xmlList = $nowPlaying->download();