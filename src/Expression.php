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

class Expression
{
    protected $queryAnd = [];
    protected $queryOr = [];

    public function search($term, $in = null)
    {
        return $this->andSearch($term, $in);
    }

    public function andSearch($term, $in = null)
    {
        $this->queryAnd[] = $this->processTerm($term, $in);;

        return $this;
    }

    public function orSearch($term, $in = null)
    {
        $this->queryOr[] = $this->processTerm($term, $in);

        return $this;
    }

    private function processTerm($term, $in)
    {
        if (isset($in)) {
            return  $in . ':' . $term;
        }

        return $term;
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
}
