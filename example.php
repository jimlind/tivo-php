<?php

include 'vendor/autoload.php';

// Locate the TiVo.
$builder   = new Symfony\Component\Process\ProcessBuilder();
$location  = new JimLind\TiVo\Location($builder);
$ipAddress = $location->find();

// Download a list of XML elements.
$ip         = '192.168.0.1';
$mak        = '7678999999';
$guzzle     = new GuzzleHttp\Client();
$nowPlaying = new JimLind\TiVo\NowPlaying($ip, $mak, $guzzle);
$xmlList    = $nowPlaying->download();
$xmlSlice   = array_slice($xmlList, 0, 2);

// Build a list of show models.
$factory  = new JimLind\TiVo\Factory\ShowListFactory();
$showList = $factory->createShowListFromXmlList($xmlSlice);

// Download the video file.
$show       = array_pop($showList);
$downloader = new JimLind\TiVo\Download($mak, $guzzle);
$downloader->store($show->getURL(), '/tmp/video.tivo');

// Download a portion of the video file.
$downloader->storePreview($show->getURL(), '/tmp/preview.tivo');

// Decode the video file.
$decoder = new JimLind\TiVo\Decode($mak, $process);
$decoder->decode('/tmp/preview.tivo', '/tmp/preview.mpeg');