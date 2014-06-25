<?php
/*
 *  Copyright 2014 Opendi Software AG
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
namespace Opendi\Solr\Client\Tests;

use Mockery as m;

use Opendi\Solr\Client\Client;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    /**
     * @expectedException Opendi\Solr\Client\SolrException
     * @expectedExceptionMessage You need to set a base_url on Guzzle client.
     */
    public function testFailureNoBasUrl()
    {
        $guzzle = m::mock('GuzzleHttp\\Client');
        $guzzle->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn(null);

        $select = new Client($guzzle);
    }


    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid core name
     */
    public function testInvalidCore()
    {
        $guzzle = m::mock('GuzzleHttp\\Client');
        $guzzle->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn('http://localhost:8983/solr/');

        $client = new Client($guzzle);
        $client->core([]);
    }

    public function testCoreStatus()
    {
        $guzzle = m::mock('GuzzleHttp\\Client');
        $response = m::mock('GuzzleHttp\\Message\\Response');

        $guzzle->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn('http://localhost:8983/solr/');

        $guzzle->shouldReceive('get')
            ->with('admin/cores?action=STATUS&wt=json')
            ->once()
            ->andReturn($response);

        $response->shouldReceive('json')
            ->andReturn(123);

        $client = new Client($guzzle);
        $status = $client->coreStatus();

        $this->assertSame(123, $status);
    }

    public function testSingleCoreStatus()
    {
        $guzzle = m::mock('GuzzleHttp\\Client');
        $response = m::mock('GuzzleHttp\\Message\\Response');

        $guzzle->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn('http://localhost:8983/solr/');

        $guzzle->shouldReceive('get')
            ->with('admin/cores?action=STATUS&wt=json&name=foo')
            ->once()
            ->andReturn($response);

        $response->shouldReceive('json')
            ->andReturn(123);

        $client = new Client($guzzle);
        $status = $client->coreStatus('foo');

        $this->assertSame(123, $status);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid core name
     */
    public function testCoreStatusInvalidName()
    {
        $guzzle = m::mock('GuzzleHttp\\Client');
        $guzzle->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn('http://localhost:8983/solr/');

        $client = new Client($guzzle);
        $client->coreStatus([]);
    }

    public function testPing()
    {
        $guzzle = m::mock('GuzzleHttp\\Client');
        $response = m::mock('GuzzleHttp\\Message\\Response');

        $guzzle->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn('http://localhost:8983/solr/');

        $guzzle->shouldReceive('get')
            ->with('admin/ping?wt=json')
            ->once()
            ->andReturn($response);

        $expected = 123;

        $response->shouldReceive('json')
            ->andReturn($expected);

        $client = new Client($guzzle);
        $actual = $client->ping();

        $this->assertSame($expected, $actual);
    }

    public function testPingCustomHandler()
    {
        $handler = "foo/bar";

        $guzzle = m::mock('GuzzleHttp\\Client');
        $response = m::mock('GuzzleHttp\\Message\\Response');

        $guzzle->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn('http://localhost:8983/solr/');

        $guzzle->shouldReceive('get')
            ->with("$handler?wt=json")
            ->once()
            ->andReturn($response);

        $expected = 123;

        $response->shouldReceive('json')
            ->andReturn($expected);

        $client = new Client($guzzle);
        $client->setPingHandler($handler);
        $actual = $client->ping();

        $this->assertSame($expected, $actual);
    }

    public function testGetEmitter()
    {
        $guzzle = m::mock('GuzzleHttp\\Client');

        $expected = new \stdClass();

        $guzzle->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn('http://localhost:8983/solr/');

        $guzzle->shouldReceive('getEmitter')
            ->once()
            ->andReturn($expected);

        $client = new Client($guzzle);
        $actual = $client->getEmitter();

        $this->assertSame($expected, $actual);
    }
}
