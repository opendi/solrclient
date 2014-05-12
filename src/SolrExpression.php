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

class SolrExpression
{
    protected $queryAnd = [];
    protected $queryOr = [];

    public function search($term, $in = null)
    {
        $value = '';
        if ($in != null) {
            $value .=  $in . ':' . $term;
        } else {
            $value = $term;
        }
        $this->queryAnd[] = $value;

        return $this;
    }

    public function andSearch($term, $in = null)
    {
        return $this->search($term, $in);
    }

    public function orSearch($term, $in = null)
    {
        $value = '';
        if ($in != null) {
            $value .=  $in . ':' . $term;
        } else {
            $value = $term;
        }
        $this->queryOr[] = $value;

        return $this;
    }

    public function render()
    {
        $result = implode('%20AND%20', $this->queryAnd);

        if (sizeOf($this->queryAnd) > 0 && sizeOf($this->queryOr) > 0) {
            $result .= '%20OR%20';
        }
        $result .= implode('%20OR%20', $this->queryOr);

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
