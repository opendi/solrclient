<?php
/*
 *  Copyright 2015 Opendi Software AG
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
namespace Opendi\Solr\Client\Query;

/**
 * A query for the DisMax query parser.
 *
 * @see https://cwiki.apache.org/confluence/display/solr/The+DisMax+Query+Parser
 */
class DisMaxSelect extends Select
{
    protected $defType = "dismax";

    /**
     * Specifies the fields in the index on which to perform the query.
     *
     * If absent, defaults to the default field.
     *
     * Each of the fields can have a boost factor assigned to increase or
     * decrease that particular field's importance in the query.
     *
     * @param  string|array $fields One or more fields on which to perform the
     *                              query.
     *
     * @return self
     */
    public function queryFields($fields)
    {
        if (is_array($fields)) {
            $fields = implode(' ', $fields);
        }

        $this->add("qf", $fields);

        return $this;
    }
}
