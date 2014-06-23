solrclient
==========

Classes for the busy PHP developer to work with Apache Solr.

Usage
-----

First construct a Guzzle HTTP client and set the base_url option to the Solr
endpoint you wish to work with. Then pass it to the SolrConnection.

```php
$guzzle = new \GuzzleHttp\Client([
    'base_url' => "http://localhost:8983/solr/entries/"
]);

$conn = new SolrConnection($guzzle);
```

It's also possible to pass some default request options, such as headers and
timeouts to the

```php
$guzzle = new \GuzzleHttp\Client([
    'base_url' => "http://localhost:8983/solr/entries/",
    'defaults' => [
        'timeout' => 10
    ]
]);

$solr = new SolrConnection($guzzle);
```

See [Guzzle documentation](http://docs.guzzlephp.org/) for all options.

