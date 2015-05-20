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

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\Response;

use Opendi\Solr\Client\Client;
use Opendi\Solr\Client\Core;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    protected function tearDown()
    {
        m::close();
    }

    public function testFactory()
    {
        $url = "www.google.com";
        $timeout = 666;
        $defaults = [
            'timeout' => $timeout
        ];

        $client = Client::factory($url, $defaults);

        $guzzle = $client->getGuzzleClient();

        $this->assertSame($url, $guzzle->getBaseURL());
        $this->assertSame($timeout, $guzzle->getDefaultOption('timeout'));
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
        $client = $this->getTestClient();
        $client->core([]);
    }

    public function testGetEmitter()
    {
        $client = $this->getTestClient();
        $expected = new \stdClass();

        $guzzle = $client->getGuzzleClient();
        $guzzle->shouldReceive('getEmitter')
            ->once()
            ->andReturn($expected);

        $actual = $client->getEmitter();

        $this->assertSame($expected, $actual);
    }

    public function testCoreFactory()
    {
        $name = 'foo';

        $client = $this->getTestClient();
        $core = $client->core($name);

        $this->assertInstanceOf(Core::class, $core);
        $this->assertSame($client, $core->getClient());
        $this->assertSame($name, $core->getName());

        // Fetching the core a second time should return the same object
        $this->assertSame($core, $client->core($name));
    }

    /**
     * Constructs a Client with a mock Guzzle client.
     *
     * @return Client
     */
    private function getTestClient()
    {
        $guzzle = m::mock('GuzzleHttp\\Client');
        $guzzle->shouldReceive('getBaseUrl')
            ->once()
            ->andReturn('http://localhost:8983/solr/');

        return new Client($guzzle);
    }

    public function testGet()
    {
        $query = ['foo' => 'bar'];
        $path = 'mrm';

        $options = ['query' => $query];

        $client = $this->getTestClient();
        $guzzle = $client->getGuzzleClient();

        $mockRequest = m::mock(Request::class);
        $mockResponse = m::mock(Response::class);

        $guzzle->shouldReceive('createRequest')
            ->once()
            ->with('GET', $path, $options)
            ->andReturn($mockRequest);

        $guzzle->shouldReceive('send')
            ->once()
            ->andReturn($mockResponse);

        $response = $client->get($path, $query);

        $this->assertSame($response, $mockResponse);
    }

    public function testPost()
    {
        $path = 'mrm';
        $query = ['foo' => 'bar'];
        $body = 'idonteven';
        $headers = ['bla' => 'tra'];

        $options = compact('query', 'body', 'headers');

        $client = $this->getTestClient();
        $guzzle = $client->getGuzzleClient();

        $mockRequest = m::mock(Request::class);
        $mockResponse = m::mock(Response::class);

        $guzzle->shouldReceive('createRequest')
            ->once()
            ->with('POST', $path, $options)
            ->andReturn($mockRequest);

        $guzzle->shouldReceive('send')
            ->once()
            ->andReturn($mockResponse);

        $response = $client->post($path, $query, $body, $headers);

        $this->assertSame($response, $mockResponse);
    }

    /**
     * @expectedException Opendi\Solr\Client\SolrException
     * @expectedExceptionMessage Solr query failed
     */
    public function testSendFailure()
    {
        $client = $this->getTestClient();
        $guzzle = $client->getGuzzleClient();

        $mockRequest = m::mock(Request::class);

        $mockRequestEx = m::mock(RequestException::class);
        $mockRequestEx->shouldReceive('hasResponse')
            ->once()->andReturn(false);

        $guzzle->shouldReceive('send')
            ->once()
            ->with($mockRequest)
            ->andThrow($mockRequestEx);

        $response = $client->send($mockRequest);
    }
}
