<?php

include 'vendor/autoload.php';

// Locate the TiVo.
$process   = new Symfony\Component\Process\Process('');
$location  = new JimLind\TiVo\Location($process);
$ipAddress = $location->find();

// Download a list of XML elements.
$ip         = '192.168.0.1';
$mak        = '7678999999';
$guzzle     = new GuzzleHttp\Client();
$nowPlaying = new JimLind\TiVo\NowPlaying($ip, $mak, $guzzle);
$xmlList    = $nowPlaying->download();

// Build a Show model.
$origin   = new JimLind\TiVo\Model\Show();
$factory  = new JimLind\TiVo\Factory\ShowFactory($origin);
$xmlPiece = array_pop($xmlList);
$show     = $factory->createFromXML($xmlPiece);

// Download the video file.
$downloader = new JimLind\TiVo\Download($mak, $guzzle);
$downloader->store($show->getURL(), '/tmp/video.tivo');