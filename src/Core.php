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
use GuzzleHttp\Exception\RequestException;

use Opendi\Lang\Json;

/**
 * Functionality which can be invoked on a Solr core.
 */
class Core
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $guzzle;

    /**
     * Name of the core.
     */
    private $name;

    public function __construct(Guzzle $guzzle, $name)
    {
        $this->guzzle = $guzzle;
        $this->name = $name;
    }

    public function select(Select $select)
    {
        $query = $select->render();
        $url = "$this->name/select?$query";

        $response = $this->guzzle->get($url);

        return (string) $response->getBody(true);
    }

    public function update(Update $update)
    {
        $query = $update->render();
        $url = "$this->name/update?$query";

        $response = $this->guzzle->post($url, [
            'body' => $update->getBody(),
            'headers' => ['Content-Type' => 'application/json']
        ]);

        return (string) $response->getBody(true);
    }

    /**
     * Returns core status info.
     *
     * @return object
     */
    public function status()
    {
        $core = $this->name;

        $data = $this->get("admin/cores", [
            "action" => "STATUS",
            "core" => $core,
        ]);

        if (empty($data['status'][$core])) {
            throw new \Exception("Core \"$core\" does not exist.");
        }

        return $data['status'][$core];
    }

    /**
     * Returns the number of records in the core.
     *
     * @return integer
     */
    public function count()
    {
        $status = $this->status();

        return $status['index']['numDocs'];
    }

    /**
     * Deletes records from the core.
     */
    public function delete($select = "*:*", $commit = true)
    {
        $core = $this->name;

        $path = "$core/update";

        $query = [
            'commit' => $commit ? "true" : "false"
        ];

        $bodyData = Json::encode([
            "delete" => [
                "query" => $select
            ]
        ]);

        return $this->post($path, $query, $bodyData);
    }

    /** Performs a GET request. */
    public function get($path, $query = [])
    {
        // Set writer type to JSON
        $query['wt'] = 'json';

        // Exectue the GET request
        try {
            $response = $this->guzzle->get($path, [
                'query' => $query
            ]);
        } catch (RequestException $ex) {
            $this->handleRequestException($ex);
        }

        // Decode and return data
        return $response->json();
    }

    /** Performs a POST request. */
    public function post($path, array $query = [], $body = null, array $headers = [])
    {
        // Set writer type to JSON
        $query['wt'] = 'json';

        // Set JSON content type
        $headers['Content-Type'] = 'application/json';

        $options = [
            'query' => $query,
            'headers' => $headers,
        ];

        if (isset($body)) {
            $options['body'] = $body;
        }

        // Exectue the POST request
        try {
            $response = $this->guzzle->post($path, $options);
        } catch (RequestException $ex) {
            $this->handleRequestException($ex);
        }

        return $response->json();
    }

    private function handleRequestException(RequestException $ex)
    {
        if ($ex->hasResponse()) {
            $response = $ex->getResponse();

            $reason = $response->getReasonPhrase();
            $code = $response->getStatusCode();
            $data = $response->json();

            $msg = $data['error']['msg'];

            throw new \Exception("Solr error HTTP $code $reason:  $msg", 0, $ex);
        }

        throw new \Exception("Solr query failed", 0, $ex);
    }
}
