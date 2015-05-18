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
use Opendi\Solr\Client\Query\Select;
use Opendi\Solr\Client\Query\Update;

/**
 * Functionality which can be invoked on a Solr core.
 */
class Core
{
    /**
     * The Solr Client, used for making requests.
     *
     * @var Opendi\Solr\Client\Client
     */
    private $client;

    /**
     * Name of the core.
     */
    private $name;

    /**
     * Path to the Ping handler.
     */
    private $pingHandler = 'admin/ping';

    public function __construct(Client $client, $name)
    {
        $this->client = $client;
        $this->name = $name;
    }

    public function select(Select $select)
    {
        $path = $this->selectPath($select);

        $response = $this->client->get($path);

        return (string) $response->getBody(true);
    }

    public function selectPath(Select $select)
    {
        $query = $select->render();

        return "$this->name/select?$query";
    }

    public function update(Update $update)
    {
        $path = $this->updatePath($update);

        $response = $this->client->post($path, [
            'body' => $update->getBody(),
            'headers' => ['Content-Type' => 'application/json']
        ]);

        return (string) $response->getBody(true);
    }

    public function updatePath(Update $update)
    {
        $query = $update->render();

        return "$this->name/update?$query";
    }

    /**
     * Returns core status info.
     *
     * @return array
     */
    public function status()
    {
        $core = $this->name;
        $path = "admin/cores";
        $query = [
            "action" => "STATUS",
            "core" => $core,
            "wt" => "json"
        ];

        $data = $this->client->get($path, $query)->json();

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
        $select = Solr::select()
            ->search('*:*')
            ->rows(0)
            ->format('json');

        $result = Json::decode($this->select($select));

        return $result->response->numFound;
    }

    /**
     * Deletes all records from the core.
     */
    public function deleteAll($commit = true)
    {
        return $this->deleteByQuery("*:*", $commit);
    }

    /**
     * Deletes records with the given ID.
     */
    public function deleteByID($id, $commit = true)
    {
        $core = $this->name;

        $path = "$core/update";

        $query = [
            "commit" => $commit ? "true" : "false",
            "wt" => "json"
        ];

        $headers = [
            'Content-Type' => 'application/json'
        ];

        $body = Json::encode([
            "delete" => [
                "id" => $id
            ]
        ]);

        return $this->client->post($path, $query, $body, $headers)->json();
    }

    /**
     * Deletes records matching the given query.
     */
    public function deleteByQuery($select, $commit = true)
    {
        $core = $this->name;

        $path = "$core/update";

        $query = [
            "commit" => $commit ? "true" : "false",
            "wt" => "json"
        ];

        $headers = [
            'Content-Type' => 'application/json'
        ];

        $body = Json::encode([
            "delete" => [
                "query" => $select
            ]
        ]);

        return $this->client->post($path, $query, $body, $headers)->json();
    }

    /**
     * Pings the server to check it's there.
     *
     * @return array Solr's reply.
     * @throws SolrException If server does not respond.
     */
    public function ping()
    {
        $path = implode('/', [$this->name, $this->pingHandler]);

        $response = $this->client->get($path, [
            'wt' => 'json'
        ]);

        return $response->json();
    }

    public function optimize()
    {
        $path = "$this->name/update";

        $query = [
            'optimize' => 'true',
            'wt' => 'json'
        ];

        $headers = [
            'Content-Type' => 'application/json'
        ];

        return $this->client->post($path, $query, $headers)->json();
    }

    /**
     * Sets the path to the Solr ping handler.
     *
     * Use a relative path, such as 'admin/ping', and not absolute like
     * '/admin/ping', otherwise it won't work when solr base is not same as root
     * url.
     *
     * @param string $handler
     * @see https://cwiki.apache.org/confluence/display/solr/Ping
     */
    public function setPingHandler($handler)
    {
        $this->pingHandler = $handler;
    }
}
