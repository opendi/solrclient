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

/**
 * Functionality which can be invoked on a Solr core.
 */
class Core
{
    /**
     * @var GuzzleHttp\Client
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
            'body' => $update->getBody()
        ]);

        return (string) $response->getBody(true);
    }
}
