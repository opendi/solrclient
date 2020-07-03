solrclient
==========

Classes for the busy PHP developer to work with Apache Solr.

[![Circle CI](https://img.shields.io/circleci/project/opendi/solrclient.svg?style=flat-square)](https://circleci.com/gh/opendi/solrclient)
[![Packagist](https://img.shields.io/packagist/v/opendi/solrclient.svg?style=flat-square)]()
[![License](https://img.shields.io/github/license/opendi/solrclient.svg)](https://github.com/opendi/solrclient/blob/develop/LICENSE)

Requirements
------------

This package requires at least PHP 5.5.9.

Construction
------------

First, you must construct a Guzzle HTTP client and set the `base_uri` option to
the Solr endpoint you wish to work with. Then use it to create a Solr Client.

```php
use Opendi\Solr\Client\Client;

$guzzle = new \GuzzleHttp\Client([
    'base_uri' => "http://localhost:8983/solr/"
]);

$client = new Client($guzzle);
```

It's also possible to pass some default request options, such as headers and
timeouts to the Guzzle client.

```php
use Opendi\Solr\Client\Client;

$guzzle = new \GuzzleHttp\Client([
    'base_uri' => "http://localhost:8983/solr/",
    'defaults' => [
        'timeout' => 10
    ]
]);

$solr = new Client($guzzle);
```

See [Guzzle documentation](http://docs.guzzlephp.org/) for all options.

There is a helper `factory($url, $defaults)` static method which does the same
as above.

```php
use Opendi\Solr\Client\Client;

$solr = Client::factory('http://localhost:8983/solr/', [
    'timeout' => 10
]);
```

Working with cores
------------------

A `core` is solar terminology for a collection of records. To select a core, use
the `core($name)` method on the Solr Client.

```php
$core = $client->core('places');

// Perform a select query
$select = Solr::select()->search('name:Franz');
$client->core('places')->select($select);

// Perform an update query
$update = Solr::update()->body('{}');
$client->core('places')->update($update);
```

The Core object offers some helper methods:

```php
// Returns core status
$client->core('places')->status();

// Returns number of documents in a core
$client->core('places')->count();

// Deletes all records in the core
$client->core('places')->deleteAll();

// Deletes records matching a selector
$client->core('places')->deleteByQuery('name:Opendi');

// Deletes record with the given ID
$client->core('places')->deleteByID('100');

// Checks the core is up
$client->core('places')->ping();

// Optimizes core documents
$client->core('places')->optimize();

// Commits inserted documents
$client->core('places')->commit();
```
