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
use Opendi\Solr\Client\Update;

class CoreTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testSelect()
    {
        $baseUrl = "http://localhost:8983/solr/";

        // Mock request objects
        $request = m::mock('GuzzleHttp\\Message\\Request');
        $request->shouldReceive('getBody')
            ->once()
            ->andReturn("Mock response body.");

        // Mock Guzzle client
        $guzzle = m::mock('GuzzleHttp\\Client');
        $guzzle->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn($baseUrl);

        $guzzle->shouldReceive('get')
            ->with("entries/select?q=" . urlencode("name:frank zappa"))
            ->once()
            ->andReturn($request);

        $select = new Select();
        $select->search('name:frank zappa');

        $client = new Client($guzzle);
        $core = $client->core('entries');
        $core->select($select);
    }

    public function testUpdate()
    {
        $baseUrl = "http://localhost:8983/solr/entries/";
        $body = '{ "id": 1 }';

        // Mock request and response objects
        $request = m::mock('GuzzleHttp\\Message\\Request');
        $request->shouldReceive('getBody')
            ->once()
            ->andReturn("Mock response body.");

        // Mock Guzzle client
        $guzzle = m::mock('GuzzleHttp\\Client');
        $guzzle->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn($baseUrl);

        $guzzle->shouldReceive('post')
            ->with("entries/update?", ['body' => $body, 'headers'=> ['Content-Type' => 'application/json']])
            ->once()
            ->andReturn($request);

        $update = new Update();
        $update->body($body);

        $client = new Client($guzzle);
        $client->core('entries')->update($update);
    }

    private function setupStatus($core, $status)
    {
        $baseUrl = "http://localhost:8983/solr/";

        // Mock request and response objects
        $request = m::mock('GuzzleHttp\\Message\\Request');
        $request->shouldReceive('json')
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
            ->andReturn($request);

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
        $status = [
            'index' => [
                'numDocs' => $count
            ]
        ];

        $client = $this->setupStatus($core, $status);
        $result = $client->core($core)->count();

        $this->assertSame($result, $count);
    }

    public function testDelete()
    {
        $baseUrl = "http://localhost:8983/solr/";
        $retval = 123;
        $core = "xxx";

        // Mock request and response
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

        $actual = $client->core($core)->delete();

        $this->assertSame($retval, $actual);
    }

    public function testDelete2()
    {
        $baseUrl = "http://localhost:8983/solr/";
        $retval = 123;
        $core = "xxx";

        $select = "name:ivan";
        $commit = false;

        // Mock request and response
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

        $actual = $client->core($core)->delete("name:ivan", false);

        $this->assertSame($retval, $actual);
    }
}
