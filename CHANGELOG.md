Opendi Solr Client Changelog
============================

0.5.0 (2015-04-07)
------------------

* Updated dependencies to Symfony components 2.6 and Guzzle 5

0.4.2 (2014-10-22)
------------------

* Fixed a bug in `bin/solr` which prevented autoloading (#8) when installed as
  a dependency.

0.4.1 (2014-10-22)
------------------

* Added bin/solr binary to composer.json so that it will be available to
  libraries which use solrclient (#7)

0.4.0 (2014-10-20)
------------------

* Added `Group` class for better sorting
* Added `Solr:group()`
* Added `Core::count()`
* Added `Core::deleteAll()`
* Added `Core::deleteByID()`
* Added `Core::deleteByQuery()`
* Added `Core::status()`
* Added `Client::factory()` for easier client construction
* Moved `Client::ping()` to `Core::ping()`
* Modified the ping command to take a core name as argument

0.3.2 (2014-08-25)
------------------

#### Features

* Added a [Pimple service provider](https://github.com/fabpot/Pimple#extending-a-container)
  for the Solr client

0.3.1 (2014-07-25)
------------------

#### Bugfixes

* Fixed solr update which didn't work by adding headers for JSON content type

0.3.0 (2014-06-23)
------------------

#### BC breaks

* Renamed classes, removed the "Solr" prefix, so `SolrClient` becomes `Client`,
  etc.
* Separated `Connection` class into `Core` and `Client`. Methods `select()` and
  `update()` methods have been moved to `Core` class. To excecute a select, run
  `$client->core('<core>')->select($select)` where '<core>' is the name of the
  core on which you want to run the query.

#### Features

* Added `Client::coreStatus()`
* Added `Client::getEmitter()`
* Added `Client::ping()`
* Added `Solr` factory class for easier chaining.

0.2.1 (2014-06-11)
------------------

* Reworked facet support, added new options such as pivot
* facet.field is no longer mandatory
* Removed __toString magic methods from SolrSelect and SolrFacet
