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
     *     ['field', 'place'],
     *     ['limit', 100]
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
     * @param array $locals
     */
    public function add($key, $value, array $locals = null)
    {
        if (!empty($locals)) {
            $locals = $this->renderLocals($locals);
            $value = $locals . $value;
        }

        $this->query[] = [$key, $value];

        return $this;
    }

    protected function renderLocals(array $locals)
    {
        $values = [];

        foreach ($locals as $key => $value) {
            if (is_bool($value)) {
                $value = $value ? "true" : "false";
            }

            if (is_numeric($key)) {
                $values[] = $value;
            } else {
                $values[] = "$key=$value";
            }
        }

        $values = implode(" ", $values);

        return "{!$values}";
    }

    /**
     * Appends all query parameters from another query to this one.
     *
     * @param  Query  $query
     */
    public function merge(Query $query)
    {
        $pairs = $query->getPairs();

        foreach ($pairs as $pair) {
            $this->query[] = $pair;
        }

        return $this;
    }

    /**
     * Returns all query parameters as an array.
     *
     * @return array
     */
    public function getPairs()
    {
        return $this->query;
    }

    public function __toString()
    {
        return $this->render();
    }

    /**
     * Builds a query string from data in $this->query.
     *
     * @return string
     */
    public function render()
    {
        return $this->renderPairs($this->getPairs());
    }

    /**
     * Returns a user-readable representation of the query.
     *
     * @return string
     */
    public function dump()
    {
        $pairs = $this->getPairs();

        $render = function ($pair) {
            return implode("=", array_map('strval', $pair));
        };

        return implode("\n", array_map($render, $pairs));
    }

    /**
     * Takes an array of pairs and renders a query string.
     *
     * @param  array  $pairs
     *
     * @return string
     */
    protected function renderPairs(array $pairs, $glue = '&')
    {
        $query = array_map([$this, 'joinPair'], $pairs);

        return implode($glue, $query);
    }

    /**
     * Takes an array with two elements, converts them to strings, urlencodes
     * them and joins them with an equals sign.
     *
     * @param  array  $pair
     *
     * @return string
     */
    protected function joinPair(array $pair)
    {
        $pair = array_map('strval', $pair);
        $pair = array_map('urlencode', $pair);

        return implode("=", $pair);
    }
}
