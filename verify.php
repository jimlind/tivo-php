<?php

$argumentList = $argv;
if (count($argumentList) < 2) {
    print(" > You need to supply your MAK as an argument.");
    finishRun();
} else {
    $mak = $argumentList[1];
}

// Require the Composer autoload file.
require 'vendor/autoload.php';

// Setup the ProcessBuilder.
validateClass('Symfony\Component\Process\ProcessBuilder');
$builder = new Symfony\Component\Process\ProcessBuilder();

// Setup the File Logger
$logFile = '/tmp/tivo_verify.log';
$logger     = new Apix\Log\Logger();
$logger->add(
    new Apix\Log\Logger\File($logFile)
);

// Locate the TiVo.
validateClass('JimLind\TiVo\TiVoFinder');
$finder = new JimLind\TiVo\TiVoFinder($builder);
$finder->setLogger($logger);
$ipAddress = $finder->find();

// Report the results of locating the TiVo.
if (empty($ipAddress) === false) {
    print(" >  Your TiVo was automatically found at `{$ipAddress}` on your network.\n");
} else {
    print(" >  Your TiVo could not be automatically located.\n");
    print(" >  Do you have an odd network setup like virtual machines or the like?\n");
}

// Exit if no IP can be used.
if (empty($ipAddress) && count($argumentList) < 3) {
    print(" >  No IP available. You can supply one manually if neccessary.\n");
    finishRun($logFile);
}

// Use the manually entered IP.
if (count($argumentList) > 2) {
    $ipAddress = $argumentList[2];
    print(" >  Using the manually entered IP of `{$ipAddress}` for your TiVo.\n");
}

// Setup the Guzzle client.
validateClass('GuzzleHttp\Client');
$guzzle = new GuzzleHttp\Client();

// Download a NowPlaying list.
validateClass('JimLind\TiVo\XmlDownloader');
print(" >  Downloading the Now Playing list from your TiVo.\n");
$xmlDownloader = new JimLind\TiVo\XmlDownloader($ipAddress, $mak, $guzzle);
$xmlDownloader->setLogger($logger);
$xmlList = $xmlDownloader->download();
if (empty($xmlList)) {
    print(" >  No proper response from your TiVo. Something is wrong.\n");
    finishRun($logFile);
} else {
    $xmlCount = count($xmlList);
    print(" >  Now Playing list of {$xmlCount} shows downloaded.\n");
}

// Translate an XML list to a Show list.
validateClass('JimLind\TiVo\Factory\ShowListFactory');
$listFactory = new JimLind\TiVo\Factory\ShowListFactory();
$showList    = $listFactory->createShowListFromXmlList($xmlList);
$showCount   = count($showList);
print(" >  Now Playing list of {$showCount} shows translated.\n");

// Grab a random Show.
$key          = rand(1, $showCount - 1);
$show         = $showList->offsetGet($key);
$showTitle    = $show->getShowTitle();
$episodeTitle = $show->getEpisodeTitle();
$showURL      = $show->getURL();

print(" >  Picked a random show from the list.\n");
print(" >  - {$showTitle} {$episodeTitle}\n");
print(" >  - {$showURL}\n");

// Identify local file locations to be deleted later.
$tivoFile = '/tmp/' . rand() . '.tivo';
$mpegFile = '/tmp/' . rand() . '.mpeg';

// Download a preview file.
validateClass('JimLind\TiVo\VideoDownloader');
print(" >  Downloading a preview of the show locally.\n");
$downloader = new JimLind\TiVo\VideoDownloader($mak, $guzzle);
$downloader->downloadPreview($showURL, $tivoFile);

// Verify file exists and has contents.
if (file_exists($tivoFile) && filesize($tivoFile) > 0) {
    print(" >  Preview file was sucessfully downloaded.\n");
} else {
    print(" >  Preview file was not sucessfully downloaded.\n");
    finishRun($logFile);
}

// Decode the preview file.
validateClass('JimLind\TiVo\VideoDecoder');
print(" >  Decoding the preview of the local show file.\n");
$decoder = new JimLind\TiVo\VideoDecoder($mak, $builder);
$decoder->decode($tivoFile, $mpegFile);

// Verify file exists and has contents.
if (file_exists($mpegFile) && filesize($mpegFile) > 0) {
    print(" >  Preview file was sucessfully decoded.\n");
} else {
    print(" >  Preview file was not sucessfully decoded.\n");
    finishRun($logFile);
}

// Delete raw tivo file.
unlink($tivoFile);
unlink($mpegFile);

print(" >  Verification completed successfully.\n");
finishRun($logFile);

/*
 * Helper function to make sure everything is properly autoloaded.
 */
function validateClass($className)
{
    if (class_exists($className) === false) {
        print(" >  Can't load {$className}.\n");
        print(" >  Did you run `composer install` already?\n");
        finishRun();
    }
}

/**
 * Helper function to display log and exit early.
 */
function finishRun($logFile = false)
{
    if ($logFile) {
        print("\n::::Log Contents::::\n");
        readfile($logFile);
        print("\n");
        unlink($logFile);
    }

    die;
}