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

class SolrFacet
{
    private $minCount = null;
    private $limit = null;
    private $fields = [];

    private $prefix = null;

    public function prefix($prefix)
    {
        $this->prefix = $prefix;
    }

    public function minCount($minCount)
    {
        $this->minCount = $minCount;

        return $this;
    }

    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    public function addField($field)
    {
        $this->fields[] = $field;

        return $this;
    }

    public function render()
    {
        if (empty($this->fields)) {
            throw new SolrException('Facets need at least on field to operate on');
        }

        $result = 'facet=true';

        if ($this->minCount != null) {
            $result .= '&facet.mincount=' . $this->minCount;
        }

        if ($this->limit != null) {
            $result .= '&facet.limit=' . $this->limit;
        }

        if ($this->prefix != null) {
            $result .= '&facet.prefix=' . $this->prefix;
        }

        $result .=  '&facet.field=' . implode('&facet.field=', $this->fields);

        return $result;
    }

    public function __toString()
    {
        try {
            return $this->render();
        } catch (\Exception $ex) {
            return "ERROR: " . $ex->getMessage();
        }
    }
}
