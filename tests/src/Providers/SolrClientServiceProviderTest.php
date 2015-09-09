<?php
/*
 *  Copyright 2015 Opendi Software AG
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing,
 *  software distributed under the License is distributed
 *  on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 *  either express or implied. See the License for the specific
 *  language governing permissions and limitations under the License.
 */
namespace Opendi\Solr\Client\Tests\Providers;

use Opendi\Solr\Client\Client;
use Opendi\Solr\Client\Providers\SolrClientServiceProvider;

class SolrClientServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $url = 'http://localhost:8983/solr/';

        $container = new \Pimple\Container();

        $provider = new SolrClientServiceProvider([
            'base_uri' => $url
        ]);

        $container->register($provider);

        $client = $container['solr'];

        $this->assertInstanceOf(Client::class, $client);

        $actual = (string) $client->getGuzzleClient()->getConfig('base_uri');
        $this->assertSame($url, $actual);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage You must specify the base_uri option.
     */
    public function testNoBaseUrl()
    {
        new SolrClientServiceProvider([]);
    }

    public function testFactory()
    {
        $url = 'http://localhost:8983/solr/';
        $user = "foo";
        $pass = "bar";
        $timeout = 13;
        $options = ['timeout' => $timeout];

        $container = new \Pimple\Container();
        $provider = SolrClientServiceProvider::factory($url, $user, $pass, $options);
        $container->register($provider);

        $client = $container['solr'];
        $this->assertInstanceOf(Client::class, $client);

        $guzzle = $client->getGuzzleClient();
        $this->assertSame($url, (string) $guzzle->getConfig('base_uri'));
        $this->assertSame([$user, $pass], $guzzle->getConfig('auth'));
        $this->assertSame($timeout, $guzzle->getConfig('timeout'));
    }
}
