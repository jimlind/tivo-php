<?php

include 'vendor/autoload.php';

// Setup the ProcessBuilder dependency.
// This is part of the the Process component from Symfony for executing commands.
$builder = new Symfony\Component\Process\ProcessBuilder();

// Setup the Guzzle dependency.
// This is the best PHP HTTP library around.
$guzzle = new GuzzleHttp\Client();

// Use the Location service to find a TiVo on your local network.
// If it can't find a TiVo for whatever reason it'll return an empty string.
// If you have more than one TiVo it'll return the IP of the first one it sees.
$location  = new JimLind\TiVo\Location($builder);
$ipAddress = $location->find();

// Use the NowPlaying service to download a list of strings representing XML show documents.
// Each show is a self contained valid XML string.
// The TiVo IP address and MAK (Media Access Key) are needed here.
$ip         = '192.168.0.1';
$mak        = '7678999999';
$nowPlaying = new JimLind\TiVo\NowPlaying($ip, $mak, $guzzle);
$xmlList    = $nowPlaying->download();

// Use the ShowListFactory to create a list of Show objects.
// The factory is setup specifically to use the data output from the NowPlaying service.
$factory  = new JimLind\TiVo\Factory\ShowListFactory();
$showList = $factory->createShowListFromXmlList($xmlList);

// Grab one Show off the top.
$show = $showList->offsetGet(0);

// Setup the Download service.
// The TiVo IP address and MAK (Media Access Key) are needed here.
$downloader = new JimLind\TiVo\Download($mak, $guzzle);

// Download preview and complete files from the TiVo.
$downloader->storePreview($show->getURL(), '/home/user/videos/raw_video_preview.tivo');
$downloader->store($show->getURL(), '/home/user/videos/raw_video_complete.tivo');

// Use the Decode service to create an MPEG file from the encoded raw TiVo file.
// It just writes the file to the new location. Doesn't touch the old file.
// The MAK (Media Access Key) is needed here.
$decoder = new JimLind\TiVo\Decode($mak, $builder);
$decoder->decode(
    '/home/user/videos/raw_video_preview.tivo',
    '/home/user/videos/raw_video_preview.mpeg'
);
