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
use GuzzleHttp\Message\Response;

use Opendi\Lang\Json;
use Opendi\Solr\Client\Client;
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
     * @var Client
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

    /**
     * Performs an update query.
     *
     * Forces wt=json and decodes the response.
     *
     * @param  Update $updat
     *
     * @return array
     */
    public function update(Update $update)
    {
        // Force resulting data to be json-encoded
        $update->format('json');

        $response = $this->updateRaw($update);

        return $response->json();
    }

    /**
     * Performs an Update query and returns the raw response.
     *
     * Unlike update(), does not force wt=json.
     *
     * @param  Update $update
     *
     * @return Response
     */
    public function updateRaw(Update $update)
    {
        $path = $this->updatePath($update);

        $body = $update->getBody();
        $contentType = $update->getContentType();

        $headers = [];
        if (isset($contentType)) {
            $headers['Content-Type'] = $contentType;
        }

        return $this->client->post($path, [], $body, $headers);
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
     * @param  string $query If given, will count documents matching the query.
     *
     * @return integer
     */
    public function count($query = "*:*")
    {
        $select = Solr::select()
            ->search($query)
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
        $body = Json::encode([
            "delete" => [
                "id" => $id
            ]
        ]);

        $update = Solr::update()
            ->body($body)
            ->commit($commit);

        return $this->update($update);
    }

    /**
     * Deletes records matching the given query.
     */
    public function deleteByQuery($select, $commit = true)
    {
        $body = Json::encode([
            "delete" => [
                "query" => $select
            ]
        ]);

        $update = Solr::update()
            ->body($body)
            ->commit($commit);

        return $this->update($update);
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

    /**
     * Optimizes the data in the core.
     *
     * @return array Decoded response from the Solr server.
     */
    public function optimize()
    {
        $update = new Update();
        $update->optimize();

        return $this->update($update);
    }

    /**
     * Commits the data in the core.
     *
     * This will make all data which was sent previously, but not commited,
     * available for search.
     *
     * @return array Decoded response from the Solr server.
     */
    public function commit()
    {
        $update = new Update();
        $update->commit();

        return $this->update($update);
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

    /**
     * Returns the core name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the underlying Solr client.
     *
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
