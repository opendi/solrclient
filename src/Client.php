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
namespace Opendi\Solr\Client;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;

use Opendi\Lang\Json;
use Opendi\Solr\Client\SolrException;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use InvalidArgumentException;

class Client
{
    /**
     * Guzzle HTTP client.
     *
     * @var GuzzleHttp\Client
     */
    private $guzzle;

    private $cores = [];

    public function __construct(Guzzle $guzzle)
    {
        $this->guzzle = $guzzle;
    }

    /**
     * Helper factory method for creating a new Client instance.
     *
     * @param  string $url      URL to the solr instance.
     * @param  array  $defaults Default options for guzzle.
     *
     * @return Client
     */
    public static function factory($url, array $defaults = [])
    {
        $defaults['base_uri'] = $url;

        $guzzle = new Guzzle($defaults);

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
     * Returns core status info.
     *
     * @param string $core Name of the core to return the status for, or null
     *                     to return status for all cores.
     */
    public function status($core = null)
    {
        $query = [
            "action" => "STATUS",
            "wt" => "json"
        ];

        if (isset($core)) {
            $query['core'] = $core;
        }

        $path = "admin/cores?" . http_build_query($query);

        $response = $this->get($path);
        $contents = $response->getBody()->getContents();
        return Json::decode($contents, true);
    }

    /**
     * Returns the underlying Guzzle client.
     *
     * @return Guzzle
     */
    public function getGuzzleClient()
    {
        return $this->guzzle;
    }

    /**
     * Performs a GET request.
     *
     * @param  string $uri
     *
     * @return Response
     */
    public function get($uri, $headers = [])
    {
        $request = $this->formGetRequest($uri, $headers);

        return $this->send($request);
    }

    public function formGetRequest($uri, $headers)
    {
        return new Request('GET', $uri, $headers);
    }

    /**
     * Performs a POST request.
     *
     * @param  string $uri
     * @param  mixed  $body
     * @param  array  $headers
     *
     * @return Response
     */
    public function post($uri, $body = null, array $headers = [])
    {
        $request = $this->formPostRequest($uri, $body, $headers);

        return $this->send($request);
    }

    public function formPostRequest($uri, $body = null, array $headers = [])
    {
        return new Request('POST', $uri, $headers, $body);
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

            $msg = $this->getSolrErrorMessage($response);
            if ($msg !== null) {
                throw new SolrException("Solr returned HTTP $code $reason: $msg", 0, $ex);
            } else {
                throw new SolrException("Solr returned HTTP $code $reason", 0, $ex);
            }
        }

        throw new SolrException("Solr query failed", 0, $ex);
    }

    /**
     * Attempt to extract a Solr error message from a response.
     *
     * @param  Response $response The response.
     * @return string Error message or NULL if not found.
     */
    private function getSolrErrorMessage(Response $response)
    {
        // Try to get the SOLR error message from the response body
        $contentType = $response->getHeaderLine("Content-Type");
        $content = $response->getBody()->getContents();

        // If response contains XML
        if (strpos($contentType, 'application/xml') !== false) {
            return $this->getXmlError($content);
        }

        // If response contains JSON
        if (strpos($contentType, 'application/json') !== false) {
            return $this->getJsonError($content);
        }

        // Message not found
        return null;
    }

    /**
     * Attempt to extract a Solr error message from an XML response.
     *
     * @param  string $content Response contents.
     * @return string Error message or NULL if not found.
     */
    private function getXmlError($content)
    {
        $xml = simplexml_load_string($content);
        if ($xml === false) {
            return null;
        }

        $msgs = $xml->xpath('lst[@name="error"]/str[@name="msg"]');
        if (!empty($msgs)) {
            return strval($msgs[0]);
        }

        return null;
    }

    /**
     * Attempt to extract a Solr error message from an JSON response.
     *
     * @param  string $content Response contents.
     * @return string Error message or NULL if not found.
     */
    private function getJsonError($content)
    {
        $data = json_decode($content);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        if (isset($data->error->msg)) {
            return $data->error->msg;
        }

        return null;
    }
}
