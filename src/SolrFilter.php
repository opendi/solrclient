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

class SolrFilter
{
    private $filters = [];

    public function filterFor($term, $in, $cache = true)
    {
        $param = '';
        if (!$cache) {
            $param = '{!cache=false}';
        }

        $this->filters[] = $param.$in . ':' . $term;

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

    public function __toString()
    {
        return $this->render();
    }
}
