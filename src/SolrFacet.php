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
    use InstanceTrait;

    const SORT_COUNT = 'count';
    const SORT_INDEX = 'index';

    // Available sorts
    private $sorts = [
        self::SORT_COUNT,
        self::SORT_INDEX,
    ];

    /**
     * Set to true the first time `$this->field()` is invoked. Used to validate
     * the query.
     */
    private $isFieldSet = false;

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
    private $query = [];

    /**
     * Identifies a field to be treated as a facet.
     *
     * It iterates over each Term in the field and generate a facet count using
     * that Term as the constraint. This parameter can be specified multiple
     * times in a query to select multiple facet fields.
     */
    public function field($field)
    {
        $this->isFieldSet = true;

        return $this->param('facet.field', $field);
    }

    /**
     * Limits the terms used for faceting to those that begin with the specified
     * prefix.
     *
     * @param  string $prefix The prefix value.
     * @param  string $field  Name of the field to apply per-field (optional).
     * @return SolrFacet
     */
    public function prefix($prefix, $field = null)
    {
        return $this->param('facet.prefix', $prefix, $field);
    }

    /**
     * Controls how faceted results are sorted.
     *
     * Possible values are:
     * - count - Sort by count (highest count first).
     * - index - Sorted in the index order (lexicographic by indexed term).
     *
     * @param  string $sort  Sort value ("count" or "index") .
     * @param  string $field Name of the field to apply per-field (optional).
     * @return SolrFacet
     */
    public function sort($sort, $field = null)
    {
        if (!in_array($sort, $this->sorts)) {
            $expected = implode(', ', $this->sorts);
            throw new SolrException("Invalid sort value \"$sort\". Expected one of: $expected.");
        }

        return $this->param('facet.sort', $sort, $field);
    }

    /**
     * Sort faceted results by count.
     *
     * Shorthand for `$this->sort('count', $field)`.
     */
    public function sortByCount($field = null)
    {
        return $this->sort('count', $field);
    }

    /**
     * Sort faceted results by index.
     *
     * Shorthand for `$this->sort('index', $field)`.
     */
    public function sortByIndex($field = null)
    {
        return $this->sort('index', $field);
    }

    /**
     * Controls how many constraints should be returned for each facet.
     *
     * This parameter specifies the maximum number of constraint counts
     * (essentially, the number of facets for a field that are returned) that
     * should be returned for the facet fields. A negative value means that Solr
     * will return unlimited number of constraint counts.
     *
     * The default value is 100.
     *
     * @param  integer $limit The limit value.
     * @param  string  $field Name of the field to apply per-field (optional).
     *
     * @return SolrFacet
     */
    public function limit($limit, $field = null)
    {
        return $this->param('facet.limit', $limit, $field);
    }

    /**
     * Sets the limit parameter to a negative value which indicates not to limit
     * the returned facet count.
     *
     * Shorthand for `$this->limit(-1, $field)`.
     *
     * @param  string  $field Name of the field to apply per-field (optional).
     *
     * @return SolrFacet
     */
    public function noLimit($field = null)
    {
        return $this->param('facet.limit', -1, $field);
    }

    /**
     * Specifies an offset into the facet results at which to begin displaying
     * facets.
     *
     * The default value is 0.
     *
     * @param  integer $offset The offset value.
     * @param  string  $field  Name of the field to apply per-field (optional).
     *
     * @return SolrFacet
     */
    public function offset($offset, $field = null)
    {
        return $this->param('facet.offset', $offset, $field);
    }

    /**
     * Specifies the minimum counts required for a facet field to be included in
     * the response.
     *
     * If a field's counts are below the minimum, the field's facet is not
     * returned.
     *
     * The default value is 0.
     *
     * @param  integer $minCount The mincount value.
     * @param  string  $field  Name of the field to apply per-field (optional).
     *
     * @return SolrFacet
     */
    public function minCount($minCount, $field = null)
    {
        return $this->param('facet.mincount', $minCount, $field);
    }

    /**
     * Defines the fields to use for the pivot. Multiple pivot values will
     * create multiple "facet_pivot" sections in the response.
     *
     * @param  string|array $fields One or more field names.
     * @return [type]         [description]
     */
    public function pivot()
    {
        $fields = func_get_args();

        if (empty($fields)) {
            throw new SolrException("At least one pivot field must be specified.");
        }

        $fields = implode(',', $fields);

        return $this->param('facet.pivot', $fields);
    }

    /**
     * Generic setter for query parameters.
     */
    private function param($name, $value, $field = null)
    {
        // Apply the parameter to a specific field
        if (!empty($field)) {
            $name = "f.$field.$name";
        }

        $this->query[] = [$name, $value];

        return $this;
    }

    /**
     * Converts the facet to a http encoded query.
     */
    public function render()
    {
        if (!$this->isFieldSet) {
            throw new SolrException("At least one facet field must be set.");
        }

        $query = [
            'facet=true'
        ];

        foreach ($this->query as $item) {
            list($name, $value) = $item;
            $query[] = implode("=", [$name, urlencode($value)]);
        }

        return implode("&", $query);
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
