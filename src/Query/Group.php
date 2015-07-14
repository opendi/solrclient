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

use Opendi\Solr\Client\Query;
use Opendi\Solr\Client\SolrException;

/**
 * Grouping query.
 *
 * @see https://cwiki.apache.org/confluence/display/solr/Result+Grouping
 */
class Group extends Query
{
    const FORMAT_GROUPED = 'grouped';
    const FORMAT_SIMPLE = 'simple';

    public function __construct()
    {
        $this->add('group', 'true');
    }

    /**
     * Identifies a field to be treated as a group.
     *
     * It iterates over each Term in the field and generate a facet count using
     * that Term as the constraint. This parameter can be specified multiple
     * times in a query to select multiple facet fields.
     * @param $field
     * @return Group
     */
    public function field($field)
    {
        return $this->add('group.field', $field);
    }

    /**
     * How to sort documents within a single group. Defaults to the same value
     * as the sort parameter.
     *
     * @param  string $sort Sort value.
     * @return Group
     */
    public function sort($sort)
    {
        return $this->add('group.sort', $sort);
    }

    /**
     * The number of results (documents) to return for each group. Defaults to 1.
     *
     * @param  integer $limit The limit value.
     *
     * @return Group
     */
    public function limit($limit)
    {
        return $this->add('group.limit', $limit);
    }

    /**
     * The offset into the document list of each group.
     *
     * @param  integer $offset The offset value.
     *
     * @return Group
     */
    public function offset($offset)
    {
        return $this->add('group.offset', $offset);
    }

    /**
     * if simple, the grouped documents are presented in a single flat list.
     * The start and rows parameters refer to numbers of documents instead
     * of numbers of groups.
     *
     * Possible Values are: grouped, simple
     *
     * @param string $format
     * @throws SolrException
     * @return Group
     */
    public function format($format)
    {
        if (!in_array($format, [self::FORMAT_GROUPED, self::FORMAT_SIMPLE])) {
            throw new SolrException("Invalid group format");
        }
        return $this->add('group.format', $format);
    }

    /**
     * If true, the result of the last field grouping command is used
     * as the main result list in the response, using group.format=simple
     *
     * @return Group
     */
    public function main()
    {
        $this->add('group.format', self::FORMAT_SIMPLE);
        return $this->add('group.main', 'true');
    }

    /**
     * If true, includes the number of groups that have matched the query.
     * Default is false. <!> Solr4.1
     *
     * WARNING: If this parameter is set to true on a sharded environment,
     * all the documents that belong to the same group have to be located
     * in the same shard, otherwise the count will be incorrect.
     *
     * If you are using SolrCloud, consider using "custom hashing"
     *
     * @return Group
     */
    public function ngroups()
    {
        return $this->add('group.ngroups', 'true');
    }

    /**
     * If true, facet counts are based on the most relevant document of each
     * group matching the query.
     *
     * Same applies for StatsComponent (http://wiki.apache.org/solr/StatsComponent).
     *
     * Default is false.
     * Supported from Solr 3.4 and up.
     *
     * @return Group
     */
    public function truncate()
    {
        return $this->add('group.truncate', 'true');
    }

    /**
     * Whether to compute grouped facets for the field facets
     * specified in facet.field parameters.
     *
     * Grouped facets are computed based on the first specified group.
     * Just like normal field faceting, fields shouldn't be tokenized
     * (otherwise counts are computed for each token).
     * Grouped faceting supports single and multivalued fields.
     * Default is false.
     * Solr4.0
     *
     * WARNING: If this parameter is set to true on a sharded environment, all the documents
     * that belong to the same group have to be located in the same shard,
     * otherwise the count will be incorrect. If you are using SolrCloud,
     * consider using "custom hashing"
     *
     * @return Group
     */
    public function facet()
    {
        return $this->add('group.facet', 'true');
    }

    /**
     * If > 0 enables grouping cache. Grouping is executed actual two searches.
     * This option caches the second search. A value of 0 disables grouping caching.
     * Default is 0.
     *
     * Tests have shown that this cache only improves search time with boolean queries,
     * wildcard queries and fuzzy queries. For simple queries like a term query or a match
     * all query this cache has a negative impact on performance
     *
     * @param integer $number
     * @return Group
     */
    public function cache($number)
    {
        return $this->add('group.cache.percent', $number);
    }
}
