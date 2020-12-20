# ACME PHP Proxy Hosting.de Provider

Package for ACME PHP Proxy [acmeproxy-php/acmeproxy-php](https://github.com/acmeproxy-php/acmeproxy-php)

## Installation

ACME PHP Proxy is installed via [Composer](https://getcomposer.org/).

```
composer require acmeproxy-php/acmeproxy-php-hostingde
```

## Usage
```
$api = new \acme\hostingde\Hostingde([
    "apiKey" => "ABC123"
]);
// Add TXT Record with Content of "abc123"
$api->present("_acme-challenge.example.org", "abc123");
// Remove TXT Record with Content of "abc123"
$api->cleanUp("_acme-challenge.example.org", "abc123");
```

## Server

If you want a complete server based on Laravel Lumen look at [acmeproxy-php/acmeproxy-php](https://github.com/acmeproxy-php/acmeproxy-php)

```
// /config/acme.php
"domains" => [
    "example.org" => "hostingde"
],
"providers" => [
    "hostingde" => [
        "apiKey" => "abc123"
    ]
],
```
