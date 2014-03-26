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


class SolrConnection
{
    private $config;
    private $endpoint;

    private $curlHandle;

    function __construct($config)
    {
        if ($config == null) {
            throw new SolrException("Configuration must not be null");
        }

        if (is_array($config)) {
            if (!isset($config['endpoint'])) {
                throw new SolrException("Configuration must not be null");
            }
            $this->endpoint = $config['endpoint'];
            $this->config = $config;
        } else {
            $this->endpoint = $config;
            $this->config = [ "endpoint" => $config ];
        }
    }

    public function connect()
    {
        $this->curlHandle = curl_init();

        curl_setopt($this->curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curlHandle, CURLOPT_HEADER, 0);
    }

    public function select(SolrSelect $select) {
        return $this->execute('select', $select->get());
    }

    public function execute($command, $query) {
        curl_setopt($this->curlHandle, CURLOPT_URL, $this->endpoint . $command . '/?' . $query);

        if (!$result = curl_exec($this->curlHandle)) {
            throw new SolrException(curl_error($this->curlHandle));
        }
        return $result;
    }

    public function disconnect() {
        curl_close($this->curlHandle);
    }
}