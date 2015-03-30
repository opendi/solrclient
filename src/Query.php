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

/**
 * A HTTP query parameter bag.
 *
 * Holds key-value pairs which can be rendered into a query string.
 *
 * Allows multiple occurances of the same key, since this is required for some
 * Solr functionalities, such as faceting.
 */
class Query
{
    /**
     * Holds the query separated into an array of two-value arrays.
     *
     * For example:
     * ```
     * $this->query = [
     *     ['field' => 'place'],
     *     ['limit' => 100]
     * ]
     * ```
     *
     * When rendered, this will become: `field=place&limit=100`.
     *
     * @var array
     */
    protected $query = [];

    /**
     * Add a new key-value pair to the query.
     *
     * @param string $key
     * @param string $value
     */
    public function add($key, $value)
    {
        $this->query[] = [$key, $value];

        return $this;
    }

    /**
     * Builds a query string matching the query.
     *
     * @return string
     */
    public function render()
    {
        $mapper = function($item) {
            return http_build_query([$item[0] => $item[1]]);
        };

        $query = array_map($mapper, $this->query);

        return implode('&', $query);
    }

    /**
     * Appends all query parameters from another query to this one.
     *
     * @param  Query  $query
     */
    public function merge(Query $query)
    {
        $pairs = $query->getAll();
        foreach ($pairs as $pair) {
            $this->query[] = $pair;
        }
    }

    /**
     * Returns all query parameters as an array.
     *
     * @return array
     */
    public function getAll()
    {
        return $this->query;
    }

    public function __toString()
    {
        return $this->render();
    }
}
