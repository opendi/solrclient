solrclient
==========

Classes for the busy PHP developer to work with Apache Solr.

Construction
------------

First, you must construct a Guzzle HTTP client and set the base_url option to
the Solr endpoint you wish to work with. Then use it to create a Solr Client.

```php
use Opendi\Solr\Client\Client;

$guzzle = new \GuzzleHttp\Client([
    'base_url' => "http://localhost:8983/solr/entries/"
]);

$client = new Client($guzzle);
```

It's also possible to pass some default request options, such as headers and
timeouts to the Guzzle client.

```php
use Opendi\Solr\Client\Client;

$guzzle = new \GuzzleHttp\Client([
    'base_url' => "http://localhost:8983/solr/entries/",
    'defaults' => [
        'timeout' => 10
    ]
]);

$solr = new Client($guzzle);
```

See [Guzzle documentation](http://docs.guzzlephp.org/) for all options.

Working with cores
------------------

A `core` is solar terminology for a collection of records. To select a core, use
the `core($name)` method on the Solr Client.

```php
$core = $client->core('places');
```

The Core object offers various helper methods:

```php
// Returns core status
$client->core('places')->status();

// Deletes all records in the core
$client->core('places')->delete();

// Deletes records matching a selector
$client->core('places')->delete('name:Opendi');

// Returns number of documents in a core
$client->core('places')->count();
```
