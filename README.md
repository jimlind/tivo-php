##tivo-php: Communicate with a S3 TiVo via Guzzle

[![Build Status](https://travis-ci.org/jimlind/tivo-php.png?branch=master)](https://travis-ci.org/jimlind/tivo-php)
[![Coverage Status](https://coveralls.io/repos/jimlind/tivo-php/badge.svg?branch=master&service=github)](https://coveralls.io/github/jimlind/tivo-php?branch=master)
[![Code Climate](https://codeclimate.com/github/jimlind/tivo-php/badges/gpa.svg)](https://codeclimate.com/github/jimlind/tivo-php)
[![Latest Stable Version](https://poser.pugx.org/jimlind/tivo-php/v/stable.svg)](https://packagist.org/packages/jimlind/tivo-php)
[![Total Downloads](https://poser.pugx.org/jimlind/tivo-php/downloads.svg)](https://packagist.org/packages/jimlind/tivo-php)
[![License](https://poser.pugx.org/jimlind/tivo-php/license.svg)](https://packagist.org/packages/jimlind/tivo-php)

#### Documentation

I find that learning by example is the best way so here is an [example file](example.php) for you to poke at.

#### Installation

This is built for, tested on, and intended to run on Ubuntu 14.04 LTS (Trusty Tahr). You can run it other distributions, but YMMV.
There are a few prerequisites you can find documented in the provided [installation file](INSTALLATION.md) for your perusal.

```sh
composer require jimlind/tivo-php
```

#### Verification

I wrote a [verification script](verify.php) for you to test your setup.
```
Usage: php verify.php MAK [IP Address]

Arguments:
- Required -- 'MAK' is the Media Access Key for your TiVo
- Optional -- 'IP Address' is the IP address of your TiVo
Example: php verify.php 7678999999 192.168.0.1
```

## Code Quality Metrics

#### 100% Code Coverage!
```sh
composer install
vendor/bin/phpunit --coverage-text
```

#### 100% Code Sniffed
```sh
composer install
bash sniff.sh
```