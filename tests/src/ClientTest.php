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

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

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

        $this->assertSame($url, strval($guzzle->getConfig('base_uri')));
        $this->assertSame($timeout, $guzzle->getConfig('timeout'));
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

        return new Client($guzzle);
    }

    public function testGetRequest()
    {
        $uri = 'mrm?foo=bar';
        $headers = ['xxx' => 'yyy'];
        $expectedHeaders = ['xxx' => ['yyy']];

        $client = $this->getTestClient();
        $request = $client->formGetRequest($uri, $headers);

        $this->assertInstanceOf(Request::class, $request);
        $this->assertSame("GET", $request->getMethod());
        $this->assertSame($uri, strval($request->getUri()));
        $this->assertSame($expectedHeaders, $request->getHeaders());
    }

    public function testGet()
    {
        $client = $this->getTestClient();
        $guzzle = $client->getGuzzleClient();

        $response = m::mock(Response::class);

        $guzzle->shouldReceive('send')
            ->once()
            ->with(m::type(Request::class))
            ->andReturn($response);

        $response = $client->get("foo");

        $this->assertSame($response, $response);
    }

    public function testPostRequest()
    {
        $uri = 'mrm?foo=bar';
        $headers = ['xxx' => 'yyy'];
        $expectedHeaders = ['xxx' => ['yyy']];
        $body = 'idonteven';

        $client = $this->getTestClient();
        $request = $client->formPostRequest($uri, $body, $headers);

        $this->assertInstanceOf(Request::class, $request);
        $this->assertSame("POST", $request->getMethod());
        $this->assertSame($uri, strval($request->getUri()));
        $this->assertSame($expectedHeaders, $request->getHeaders());
        $this->assertSame($body, $request->getBody()->getContents());
    }

    public function testPost()
    {
        $client = $this->getTestClient();
        $guzzle = $client->getGuzzleClient();

        $response = m::mock(Response::class);

        $guzzle->shouldReceive('send')
            ->once()
            ->with(m::type(Request::class))
            ->andReturn($response);

        $response = $client->post("foo");

        $this->assertSame($response, $response);
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
