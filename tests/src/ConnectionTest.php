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

use Opendi\Solr\Client\Connection;
use Opendi\Solr\Client\Select;
use Opendi\Solr\Client\Update;

class ConnectionTest extends \PHPUnit_Framework_TestCase
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

        $select = new Connection($guzzle);
    }

    public function testSelect()
    {
        $baseUrl = "http://localhost:8983/solr/entries/";

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
            ->with("select?q=name:frank zappa")
            ->once()
            ->andReturn($request);

        $select = new Select();
        $select->search('name:frank zappa');

        $conn = new Connection($guzzle);
        $conn->select($select);
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
            ->with("update/json?", ['body' => $body])
            ->once()
            ->andReturn($request);

        $update = new Update();
        $update->body($body);

        $conn = new Connection($guzzle);
        $conn->update($update);
    }
}
