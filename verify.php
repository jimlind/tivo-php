<?php

// Require the Composer autoload file.
require 'vendor/autoload.php';

// Setup the ProcessBuilder
if (class_exists('Symfony\Component\Process\ProcessBuilder') === false) {
    print(" >  Can't load Symfony\Component\Process\ProcessBuilder.\n");
    print(" >  Did you run `composer install` already?\n");
    die;
}
$builder = new Symfony\Component\Process\ProcessBuilder();

$loggerFile = '/tmp/tivo_verify.log';
$logger     = new Apix\Log\Logger();
$logger->add(
    new Apix\Log\Logger\File($loggerFile)
);

// Locate the TiVo.
if (class_exists('JimLind\TiVo\Location') === false) {
    print(" >  Can't load JimLind\TiVo\Location.\n");
    print(" >  Did you run `composer install` already?\n");
    die;
}
$location  = new JimLind\TiVo\Location($builder);
$location->setLogger($logger);
$ipAddress = $location->find();

// Report the results of locating the TiVo.
if (empty($ipAddress) === false) {
    print(" >  Your TiVo was automatically found at `{$ipAddress}` on your network.\n");
} else {
    print(" >  Your TiVo could not be automatically located.\n");
    print(" >  Do you have an odd network setup like virtual machines or the like?\n");
}

print("\n::::Logger Contents::::\n");
readfile($loggerFile);
print("\n");
unlink($loggerFile);