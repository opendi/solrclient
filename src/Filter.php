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

class Filter
{
    private $filters = [];

    /**
     * TODO:
     * Refactor.
     *
     * This is a valid filter:
     *
     * select?q=*:*&fq={!geofilt}&sort=geodist()+asc
     *
     * It should be possible to add filters like {!cache=false} with just using constants.
     *
     * Also, filter queries support multiple parameters.
     *
     * They should be supported raw, but also have some support for creating complex filter queries.
     *
     * More info: http://wiki.apache.org/solr/CommonQueryParameters#fq
     */
    public function filterFor($term, $in = null, $cache = true)
    {
        $param = '';
        if (!$cache) {
            $param = '{!cache=false}';
        }

        if ($in == null) {
            $this->filters[] = $param.$term;
        } else {
            $this->filters[] = $param.$in . ':' . $term;
        }

        return $this;
    }

    public function render()
    {
        $prefixed = [];
        foreach ($this->filters as $filter) {
            $prefixed[] = 'fq=' . $filter;
        }

        return implode('&', $prefixed);
    }
}
