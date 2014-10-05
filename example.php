<?php

include 'vendor/autoload.php';

// Locate the TiVo
$process = new Symfony\Component\Process\Process('');
$location = new JimLind\TiVo\Location($process);
$ipAddress = $location->find();

// Download a list of XML Elements
$ip  = '192.168.0.1';
$mak = '7678999999';
$guzzle = new GuzzleHttp\Client();
$nowPlaying = new JimLind\TiVo\NowPlaying($ip, $mak, $guzzle);
$xmlList = $nowPlaying->download();