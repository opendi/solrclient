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
namespace Opendi\Solr\Client;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Exception\RequestException;

use Opendi\Solr\Client\SolrException;

use InvalidArgumentException;

class Client
{
    private $guzzle;

    private $cores = [];

    public function __construct(Guzzle $guzzle)
    {
        $this->guzzle = $guzzle;

        // Check a base url has been set
        $base = $this->guzzle->getBaseUrl();
        if (empty($base)) {
            throw new SolrException("You need to set a base_url on Guzzle client.");
        }
    }

    /**
     * Helper factory method for creating a new Client instance.
     *
     * @param  string $url      URL to the solr instance.
     * @param  array  $defaults Default options for guzzle.
     *
     * @return Opendi\Solr\Client\Client
     */
    public static function factory($url, array $defaults = [])
    {
        $guzzle = new Guzzle([
            'base_url' => $url,
            'defaults' => $defaults
        ]);

        return new self($guzzle);
    }

    /**
     * Returns a Core object for the given core name.
     *
     * @param  String $name
     * @return Core
     */
    public function core($name)
    {
        if (!is_string($name) || empty($name)) {
            throw new InvalidArgumentException("Invalid core name.");
        }

        if (!isset($this->cores[$name])) {
            $this->cores[$name] = new Core($this, $name);
        }

        return $this->cores[$name];
    }

    /**
     * Returns the underlying Guzzle client's event emitter.
     *
     * @return GuzzleHttp\Event\EmitterInterface
     *
     * @see  http://guzzle.readthedocs.org/en/latest/events.html
     */
    public function getEmitter()
    {
        return $this->guzzle->getEmitter();
    }

    /**
     * Returns the underlying Guzzle client.
     *
     * @return GuzzleHttp\Client
     */
    public function getGuzzleClient()
    {
        return $this->guzzle;
    }

    /**
     * Performs a GET request.
     *
     * @param  string $url
     * @param  array  $query
     *
     * @return GuzzleHttp\Message\Response
     */
    public function get($url, array $query = [])
    {
        $options = [
            'query' => $query
        ];

        $request = $this->guzzle->createRequest('GET', $url, $options);

        return $this->send($request);
    }

    /**
     * Performs a POST request.
     *
     * @param  string $url
     * @param  array  $query
     * @param  mixed  $body
     * @param  array  $headers
     *
     * @return GuzzleHttp\Message\Response
     */
    public function post($url, array $query = [], $body = null, array $headers = [])
    {
        $options = [
            'query' => $query,
            'headers' => $headers,
            'body' => $body
        ];

        $request = $this->guzzle->createRequest('POST', $url, $options);

        return $this->send($request);
    }

    public function send(RequestInterface $request)
    {
        try {
            $response = $this->guzzle->send($request);
        } catch (RequestException $ex) {
            $this->handleRequestException($ex);
        }

        return $response;
    }

    private function handleRequestException(RequestException $ex)
    {
        if ($ex->hasResponse()) {
            $response = $ex->getResponse();
            $code = $response->getStatusCode();
            $reason = $response->getReasonPhrase();

            $msg = $this->getResponseErrorMessage($response);
            if ($msg !== null) {
                throw new SolrException("Solr returned HTTP $code $reason: $msg", 0, $ex);
            } else {
                throw new SolrException("Solr returned HTTP $code $reason", 0, $ex);
            }
        }

        throw new SolrException("Solr query failed", 0, $ex);
    }

    private function getResponseErrorMessage(Response $response)
    {
        // Try to get the SOLR error message from the response body
        $contentType = $response->getHeader("Content-Type");

        // If response contains XML
        if (strpos($contentType, 'application/xml') !== false) {
            $msgs = $response->xml()->xpath('lst[@name="error"]/str[@name="msg"]');
            if (!empty($msgs)) {
                return strval($msgs[0]);
            }
        }

        // If response contains JSON
        if (strpos($contentType, 'application/json') !== false) {
            $data = $response->json();
            if (isset($data['error']['msg'])) {
                return $data['error']['msg'];
            }
        }

        // Message not found
        return null;
    }
}
