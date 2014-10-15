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
use Opendi\Solr\Client\Select;
use Opendi\Solr\Client\Solr;
use Opendi\Solr\Client\Update;
use Opendi\Solr\Client\Core;

use Opendi\Lang\Json;

class CoreTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testSelect()
    {
        $select = Solr::select()->search('name:frank zappa');
        $query = $select->render();

        $coreName = "foo";
        $path = "$coreName/select?$query";
        $expected = "123";

        $mockResponse = m::mock('GuzzleHttp\\Message\\Response');
        $mockResponse->shouldReceive('getBody')
            ->once()
            ->andReturn($expected);

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('get')
            ->once()
            ->with($path)
            ->andReturn($mockResponse);

        $core = new Core($mockClient, $coreName);
        $actual = $core->select($select);

        $this->assertSame($expected, $actual);
    }

    public function testUpdate()
    {
        $coreName = "foo";
        $body = '{ "id": 1 }';
        $update = Solr::update()->body($body)->commit(true);
        $query = $update->render();
        $path = "$coreName/update?$query";
        $expected = "123";

        $options = [
            'body' => $body,
            'headers' => ['Content-Type' => 'application/json']
        ];

        $mockResponse = m::mock('GuzzleHttp\\Message\\Response');
        $mockResponse->shouldReceive('getBody')
            ->once()
            ->andReturn($expected);

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('post')
            ->once()
            ->with($path, $options)
            ->andReturn($mockResponse);

        $core = new Core($mockClient, $coreName);
        $actual = $core->update($update);

        $this->assertSame($expected, $actual);
    }

    private function setupStatus($core, $status)
    {
        $baseUrl = "http://localhost:8983/solr/";

        // Mock response
        $response = m::mock('GuzzleHttp\\Message\\Response');
        $response->shouldReceive('json')
            ->once()
            ->andReturn([
                'status' => [
                    $core => $status
                ]
            ]);

        // Mock Guzzle client
        $guzzle = m::mock('GuzzleHttp\\Client');
        $guzzle->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn($baseUrl);

        $guzzle->shouldReceive('get')
            ->with("admin/cores", [
                'query' => [
                    'action' => 'STATUS',
                    'core' => $core,
                    'wt' => 'json'
                ]
            ])
            ->once()
            ->andReturn($response);

        return new Client($guzzle);
    }

    public function testStatus()
    {
        $core = "foo";
        $count = 123;
        $status = [
            'index' => [
                'numDocs' => $count
            ]
        ];

        $client = $this->setupStatus($core, $status);
        $result = $client->core($core)->status();

        $this->assertSame($result, $status);
    }

    public function testCount()
    {
        $core = "foo";
        $count = 123;

        $response = Json::encode([
            'response' => [
                'numFound' => $count
            ]
        ]);

        $coreName = "foo";
        $path = "$coreName/select?q=" . urlencode("*:*") . "&wt=json&rows=0";
        $expected = "123";

        $mockResponse = m::mock('GuzzleHttp\\Message\\Response');
        $mockResponse->shouldReceive('getBody')
            ->once()
            ->andReturn($response);

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('get')
            ->once()
            ->with($path)
            ->andReturn($mockResponse);

        $core = new Core($mockClient, $coreName);
        $actual = $core->count();

        $this->assertSame($actual, $count);
    }

    public function testDeleteAll()
    {
        $baseUrl = "http://localhost:8983/solr/";
        $retval = 123;
        $core = "xxx";

        // Mock response
        $response = m::mock('GuzzleHttp\\Message\\Response');
        $response->shouldReceive('json')
            ->once()
            ->andReturn($retval);

        // Mock Guzzle client
        $guzzle = m::mock('GuzzleHttp\\Client');
        $guzzle->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn($baseUrl);

        $path = "$core/update";
        $options = [
            'query' => [
                'commit' => 'true',
                'wt' => 'json',
            ],
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => '{"delete":{"query":"*:*"}}'
        ];

        $guzzle->shouldReceive('post')
            ->with($path, $options)
            ->once()
            ->andReturn($response);

        $client = new Client($guzzle);

        $actual = $client->core($core)->deleteAll();

        $this->assertSame($retval, $actual);
    }

    public function testDeleteByQuery()
    {
        $baseUrl = "http://localhost:8983/solr/";
        $retval = 123;
        $core = "xxx";

        $select = "name:ivan";
        $commit = false;

        // Mock response
        $response = m::mock('GuzzleHttp\\Message\\Response');
        $response->shouldReceive('json')
            ->once()
            ->andReturn($retval);

        // Mock Guzzle client
        $guzzle = m::mock('GuzzleHttp\\Client');
        $guzzle->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn($baseUrl);

        $path = "$core/update";
        $options = [
            'query' => [
                'commit' => 'false',
                'wt' => 'json',
            ],
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => '{"delete":{"query":"' . $select . '"}}'
        ];

        $guzzle->shouldReceive('post')
            ->with($path, $options)
            ->once()
            ->andReturn($response);

        $client = new Client($guzzle);

        $actual = $client->core($core)->deleteByQuery("name:ivan", false);

        $this->assertSame($retval, $actual);
    }

    public function testDeleteByID()
    {
        $baseUrl = "http://localhost:8983/solr/";
        $retval = 123;
        $core = "xxx";

        $id = 666;
        $commit = false;

        // Mock response
        $response = m::mock('GuzzleHttp\\Message\\Response');
        $response->shouldReceive('json')
            ->once()
            ->andReturn($retval);

        // Mock Guzzle client
        $guzzle = m::mock('GuzzleHttp\\Client');
        $guzzle->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn($baseUrl);

        $path = "$core/update";
        $options = [
            'query' => [
                'commit' => 'false',
                'wt' => 'json',
            ],
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'body' => '{"delete":{"id":' . $id . '}}'
        ];

        $guzzle->shouldReceive('post')
            ->with($path, $options)
            ->once()
            ->andReturn($response);

        $client = new Client($guzzle);

        $actual = $client->core($core)->deleteByID($id, false);

        $this->assertSame($retval, $actual);
    }

    public function testPing()
    {
        $coreName = "xyz";
        $expected = "expected response";

        $response = m::mock('GuzzleHttp\\Message\\Response');
        $response->shouldReceive('json')
            ->andReturn($expected);

        $client = m::mock(Client::class);
        $client->shouldReceive('get')
            ->with("$coreName/admin/ping", ["wt" => "json"])
            ->once()
            ->andReturn($response);

        $core = new Core($client, $coreName);
        $actual = $core->ping();

        $this->assertSame($expected, $actual);
    }

    public function testPingCustomHandler()
    {
        $coreName = "xyz";
        $expected = "expected response";
        $handler = "foo/bar";

        $response = m::mock('GuzzleHttp\\Message\\Response');
        $response->shouldReceive('json')
            ->andReturn($expected);

        $client = m::mock(Client::class);
        $client->shouldReceive('get')
            ->with("$coreName/$handler", ["wt" => "json"])
            ->once()
            ->andReturn($response);

        $core = new Core($client, $coreName);
        $core->setPingHandler($handler);
        $actual = $core->ping();

        $this->assertSame($expected, $actual);
    }
}
