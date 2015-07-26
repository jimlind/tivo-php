<?php

include 'vendor/autoload.php';

// Setup the ProcessBuilder dependency.
// This is part of the the Process component from Symfony for executing commands.
$builder = new Symfony\Component\Process\ProcessBuilder();

// Setup the Guzzle dependency.
// This is the best PHP HTTP library around.
$guzzle = new GuzzleHttp\Client();

// Use the TiVoFinder service to find a TiVo on your local network.
// If it can't find a TiVo for whatever reason it'll return an empty string.
// If you have more than one TiVo it'll return the IP of the first one it sees.
$tivoFinder = new JimLind\TiVo\TiVoFinder($builder);
$ipAddress  = $tivoFinder->find();

// Use the XmlDownloader service to download a list of strings representing XML show documents.
// Each show is a self contained valid XML string.
// The TiVo IP address and MAK (Media Access Key) are needed here.
$ip            = '192.168.0.1';
$mak           = '7678999999';
$xmlDownloader = new JimLind\TiVo\XmlDownloader($ip, $mak, $guzzle);
$xmlList       = $xmlDownloader->download();

// Use the ShowListFactory to create a list of Show objects.
// The factory is setup specifically to use the data output from the NowPlaying service.
$showListFactory = new JimLind\TiVo\Factory\ShowListFactory();
$showList        = $showListFactory->createShowListFromXmlList($xmlList);

// Grab one Show off the top.
$show = $showList->offsetGet(0);

// Setup the VideoDownloader service.
// The TiVo IP address and MAK (Media Access Key) are needed here.
$videoDownloader = new JimLind\TiVo\VideoDownloader($mak, $guzzle);

// Download preview and complete files from the TiVo.
$videoDownloader->downloadPreview($show->getURL(), '/home/user/videos/raw_video_preview.tivo');
$videoDownloader->download($show->getURL(), '/home/user/videos/raw_video_complete.tivo');

// Use the VideoDecoder service to create an MPEG file from the encoded raw TiVo file.
// It just writes the file to the new location. Doesn't touch the old file.
// The MAK (Media Access Key) is needed here.
$videoDecoder = new JimLind\TiVo\VideoDecoder($mak, $builder);
$videoDecoder->decode(
    '/home/user/videos/raw_video_preview.tivo',
    '/home/user/videos/raw_video_preview.mpeg'
);