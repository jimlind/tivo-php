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
$xmlSlice   = array_slice($xmlList, 0, 2);

// Build a list of show models.
$origin   = new JimLind\TiVo\Model\Show();
$factory  = new JimLind\TiVo\Factory\ShowFactory($origin);
$showList = $factory->createFromXmlList($xmlSlice);

// Download the video file.
$show       = array_pop($showList);
$downloader = new JimLind\TiVo\Download($mak, $guzzle);
$downloader->store($show->getURL(), '/tmp/video.tivo');

// Decode the video file.
$decoder = new JimLind\TiVo\Decode($mak, $process);
$decoder->decode('/tmp/video.tivo', '/tmp/video.mpeg');