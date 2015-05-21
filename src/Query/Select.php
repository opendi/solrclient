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

/**
 * A query for the Standard (Lucene) query parser.
 */
class Select extends Query
{
    use Traits\SpatialTrait;

    const FORMAT_JSON = 'json';
    const FORMAT_XML = 'xml';
    const FORMAT_RUBY = 'ruby';
    const FORMAT_PHP = 'php';
    const FORMAT_CSV = 'csv';

    protected $defType;

    /**
     * Defines a query using standard query syntax. This parameter is mandatory.
     *
     * @param  string $value The value to search for.
     * @param  string $field The field to look in, if not given will default to
     *                       the default field from schema.xml or the "df"
     *                       option.
     *
     * @see defaultField()
     *
     * @return Select
     */
    public function search($value, $field = null)
    {
        $value = isset($field) ? "$field:$value" : $value;

        $this->add('q', $value);

        return $this;
    }

    /**
     * Specifies a default field, overriding the definition of a default field
     * in the schema.xml file.
     *
     * @param  string $field Field name.
     *
     * @return Select
     */
    public function defaultField($field)
    {
        $this->add('df', $field);

        return $this;
    }

    /**
     * Specifies the default operator for query expressions, overriding the
     * default operator specified in the schema.xml file. Possible values are
     * "AND" or "OR".
     *
     * @param string $op  The operator, AND or OR.
     *
     * @return Select
     */
    public function defaultOperator($op)
    {
        $this->add('q.op', $field);
    }

    /**
     * If the indent parameter is used, and has a non-blank value, then Solr
     * will make some attempts at indenting its XML or JSON response to make it
     * more readable by humans.
     *
     * The default behavior is not to indent.
     *
     * @return Select
     */
    public function indent()
    {
        $this->add('indent', 'true');

        return $this;
    }

    /**
     * Request additional debugging information in the response.
     *
     * Debug parameter can have the following values:
     * - query   - return debug information about the query only.
     * - timing  - return debug information about how long the query took to
     *             process.
     * - results - return "explain" information for each of the documents
     *             returned
     *  - all    - return all available debug information (default)
     *
     * @param  string $debug  The debug value.
     *
     * @return Select
     */
    public function debug($debug = 'all')
    {
        $this->add('debug', $debug);

        return $this;
    }

    /**
     * Applies a filter query to the search results.
     *
     * @see https://cwiki.apache.org/confluence/display/solr/Common+Query+Parameters#CommonQueryParameters-Thefq%28FilterQuery%29Parameter
     *
     * @param  string      $filter Filter value.
     * @param  array|null  $locals Local parameters.
     *
     * @return Select
     */
    public function filter($filter, array $locals = null)
    {
        $this->add('fq', $filter, $locals);

        return $this;
    }

    /**
     * Limits the information included in a query response to a specified list
     * of fields.
     *
     * The fields need to have been indexed as stored for this parameter to work
     * correctly.
     *
     * @see https://cwiki.apache.org/confluence/display/solr/Common+Query+Parameters#CommonQueryParameters-Thefl%28FieldList%29Parameter
     *
     * @param  array|string  $fields A list of fields. If an array is given,
     *                               fields will be imploded with comma as
     *                               sparator.
     *
     * @return Select
     */
    public function fieldList($fields)
    {
        if (is_array($fields)) {
            $fields = implode(',', $fields);
        }

        $this->add('fl', $fields);

        return $this;
    }

    /**
     * Specifies an offset (by default, 0) into the responses at which Solr
     * should begin displaying content.
     *
     * @see https://cwiki.apache.org/confluence/display/solr/Common+Query+Parameters#CommonQueryParameters-ThestartParameter
     *
     * @param  integer $start Starting offset.
     *
     * @return Select
     */
    public function start($start)
    {
        $this->add('start', $start);

        return $this;
    }

    /**
     * Sorts the response to a query in either ascending or descending order
     * based on the response's score or another specified characteristic.
     *
     * @see https://cwiki.apache.org/confluence/display/solr/Common+Query+Parameters#CommonQueryParameters-ThesortParameter
     *
     * @param  string $sort The sort parameter.
     *
     * @return Select
     */
    public function sort($sort)
    {
        $this->add('sort', $sort);

        return $this;
    }

    /**
     * Controls how many rows of responses are displayed at a time (default
     * value: 10)
     *
     * @see https://cwiki.apache.org/confluence/display/solr/Common+Query+Parameters#CommonQueryParameters-TherowsParameter
     *
     * @param  integer $rows Number of rows to display.
     *
     * @return Select
     */
    public function rows($rows)
    {
        $this->add('rows', $rows);

        return $this;
    }

    /**
     * Specifies the Response Writer to be used to format the query response.
     *
     * @see https://cwiki.apache.org/confluence/display/solr/Response+Writers
     *
     * @param  string $format The response writer to use.
     *
     * @return Select
     */
    public function format($format)
    {
        $this->add('wt', $format);

        return $this;
    }

    /**
     * Merges parameters from a Facet query into this query.
     *
     * @param  Facet  $facet The Facet query to merge.
     *
     * @return Select
     */
    public function facet(Facet $facet)
    {
        $this->merge($facet);

        return $this;
    }

    /**
     * Merges parameters from a Group query into this query.
     *
     * @param  Group  $group The Group query to merge.
     *
     * @return Select
     */
    public function group(Group $group)
    {
        $this->merge($group);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPairs()
    {
        $query = $this->query;

        // Add the query parser to the query, if specified
        if (isset($this->defType)) {
            array_unshift($query, ['defType', $this->defType]);
        }

        return $query;
    }
}
