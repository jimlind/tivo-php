##tivo-php: Communicate with a S3 TiVo via Guzzle

[![Build Status](https://travis-ci.org/jimlind/tivo-php.png?branch=master)](https://travis-ci.org/jimlind/tivo-php)
[![Latest Stable Version](https://poser.pugx.org/jimlind/tivo-php/v/stable.svg)](https://packagist.org/packages/jimlind/tivo-php)
[![Total Downloads](https://poser.pugx.org/jimlind/tivo-php/downloads.svg)](https://packagist.org/packages/jimlind/tivo-php)
[![License](https://poser.pugx.org/jimlind/tivo-php/license.svg)](https://packagist.org/packages/jimlind/tivo-php)

If you want to locate the TiVo on your network the Avahi daemon needs to be installed.

```sh
sudo apt-get install avahi-utils
```

To get Guzzle to work you'll need PHP's implementation of cURL

```sh
sudo apt-get install php5-curl
```
### 100% Code Coverage!
```sh
phpunit --coverage-text
```