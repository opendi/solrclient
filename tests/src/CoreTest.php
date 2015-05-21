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
namespace Opendi\Solr\Client\Tests;

use Mockery as m;

use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;

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

        $mockResponse = m::mock(Response::class);
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
        $contentType = 'application/json';

        $update = Solr::update()
            ->body($body)
            ->contentType($contentType)
            ->commit();

        $query = $update->render();
        $path = "$coreName/update?$query&wt=json";
        $expected = "123";
        $headers = ['Content-Type' => 'application/json'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('json')
            ->once()
            ->andReturn($expected);

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('post')
            ->once()
            ->with($path, [], $body, $headers)
            ->andReturn($mockResponse);

        $core = new Core($mockClient, $coreName);
        $actual = $core->update($update);

        $this->assertSame($expected, $actual);
    }

    public function testUpdateRaw()
    {
        $coreName = "foo";
        $body = '{ "id": 1 }';
        $contentType = 'application/json';

        $update = Solr::update()
            ->body($body)
            ->contentType($contentType)
            ->commit();

        $query = $update->render();
        $path = "$coreName/update?$query";
        $expected = new \stdClass();
        $headers = ['Content-Type' => 'application/json'];

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('post')
            ->once()
            ->with($path, [], $body, $headers)
            ->andReturn($expected);

        $core = new Core($mockClient, $coreName);
        $actual = $core->updateRaw($update);

        $this->assertSame($expected, $actual);
    }

    public function testStatus()
    {
        $coreName = "foo";
        $count = 123;
        $coreStatus = ['foo' => 'bar'];

        $status = [
            'status' => [
                $coreName => $coreStatus
            ]
        ];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('json')
            ->once()
            ->andReturn($status);

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('get')
            ->once()
            ->with("admin/cores", ['action' => 'STATUS', 'core' => 'foo', 'wt' => 'json'])
            ->andReturn($mockResponse);

        $core = new Core($mockClient, $coreName);
        $actual = $core->status();

        $this->assertSame($coreStatus, $actual);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Core "foo" does not exist.
     */
    public function testStatusNonexistantCore()
    {
        $coreName = "foo";
        $count = 123;
        $coreStatus = ['foo' => 'bar'];

        $status = [
            'status' => []
        ];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('json')
            ->once()
            ->andReturn($status);

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('get')
            ->once()
            ->with("admin/cores", ['action' => 'STATUS', 'core' => 'foo', 'wt' => 'json'])
            ->andReturn($mockResponse);

        $core = new Core($mockClient, $coreName);
        $actual = $core->status();

        $this->assertSame($coreStatus, $actual);
    }

    public function testCount()
    {
        $count = 123;

        $response = Json::encode([
            'response' => [
                'numFound' => $count
            ]
        ]);

        $coreName = "foo";
        $path = "$coreName/select?q=" . urlencode("*:*") . "&rows=0&wt=json";
        $expected = "123";

        $mockResponse = m::mock(Response::class);
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
        $retval = 123;
        $coreName = "xxx";

        $path = "$coreName/update?commit=true&wt=json";
        $body = '{"delete":{"query":"*:*"}}';
        $headers = ['Content-Type' => 'application/json'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('json')
            ->once()
            ->andReturn($retval);

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('post')
            ->once()
            ->with($path, [], $body, $headers)
            ->andReturn($mockResponse);

        $core = new Core($mockClient, $coreName);

        $actual = $core->deleteAll();

        $this->assertSame($retval, $actual);
    }

    public function testDeleteByQuery()
    {
        $retval = 123;
        $coreName = "xxx";
        $select = "name:ivan";

        $path = "$coreName/update?commit=true&wt=json";
        $body = json_encode(["delete" => ["query" => $select]]);
        $headers = ['Content-Type' => 'application/json'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('json')
            ->once()
            ->andReturn($retval);

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('post')
            ->once()
            ->with($path, [], $body, $headers)
            ->andReturn($mockResponse);

        $core = new Core($mockClient, $coreName);

        $actual = $core->deleteByQuery($select);

        $this->assertSame($retval, $actual);
    }

    public function testDeleteByID()
    {
        $id = 666;
        $retval = 123;
        $coreName = "xxx";
        $select = "name:ivan";

        $path = "$coreName/update?commit=true&wt=json";
        $body = json_encode(["delete" => ["id" => $id]]);
        $headers = ['Content-Type' => 'application/json'];

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('json')
            ->once()
            ->andReturn($retval);

        $mockClient = m::mock(Client::class);
        $mockClient->shouldReceive('post')
            ->once()
            ->with($path, [], $body, $headers)
            ->andReturn($mockResponse);

        $core = new Core($mockClient, $coreName);

        $actual = $core->deleteByID($id);

        $this->assertSame($retval, $actual);
    }

    public function testPing()
    {
        $coreName = "xyz";
        $expected = "expected response";

        $response = m::mock(Response::class);
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

        $response = m::mock(Response::class);
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

    public function testCommit()
    {
        $coreName = "xyz";
        $expected = "expected response";
        $headers = ['Content-Type' => 'application/json'];

        $response = m::mock(Response::class);
        $response->shouldReceive('json')
            ->andReturn($expected);

        $client = m::mock(Client::class);
        $client->shouldReceive('post')
            ->with("$coreName/update?commit=true&wt=json", [], null, $headers)
            ->once()
            ->andReturn($response);

        $core = new Core($client, $coreName);
        $actual = $core->commit();

        $this->assertSame($expected, $actual);
    }

    public function testOptimize()
    {
        $coreName = "xyz";
        $expected = "expected response";
        $headers = ['Content-Type' => 'application/json'];

        $response = m::mock(Response::class);
        $response->shouldReceive('json')
            ->andReturn($expected);

        $client = m::mock(Client::class);
        $client->shouldReceive('post')
            ->with("$coreName/update?optimize=true&wt=json", [], null, $headers)
            ->once()
            ->andReturn($response);

        $core = new Core($client, $coreName);
        $actual = $core->optimize();

        $this->assertSame($expected, $actual);
    }
}
