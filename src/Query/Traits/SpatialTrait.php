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
namespace Opendi\Solr\Client\Query\Traits;

use Opendi\Solr\Client\Query\Select;

/**
 * Adds spatial search methods to a Select query.
 */
trait SpatialTrait
{
    /**
     * Sets the radial distance from center point, usually in kilometers.
     * (RPT & BBoxField can set other units via the setting distanceUnits)
     *
     * @param  float $distance  Distance from center point in km
     *
     * @return Select
     */
    public function distance($distance)
    {
        $this->add('d', $distance);

        return $this;
    }

    /**
     * Sets the center point.
     *
     * @param  float $lat  Latitude of the center point
     * @param  float $lon  Longitude of the center point
     *
     * @return Select
     */
    public function centerPoint($lat, $lon)
    {
        $this->add('pt', "$lat,$lon");

        return $this;
    }

    /**
     * Sets the spatial field used for spatial searches.
     *
     * @param  string $field Name of the spatial field to use.
     *
     * @return Select
     */
    public function spatialField($field)
    {
        $this->add('sfield', $field);

        return $this;
    }


    /**
     * Filters results by distance.
     *
     * Requires the following parameters to be set:
     * - spatialField
     * - centerPoint
     * - distance
     *
     * @param  string $spatialField Overrides the spatialField parameter
     *                              (optional).
     *
     * @return Select
     */
    public function filterByDistance($spatialField = null)
    {
        $locals = ['geofilt'];

        if (isset($spatialField)) {
            $locals['sfield'] = $spatialField;
        }

        $this->add("fq", "", $locals);

        return $this;
    }

    public function sortByDistance($direction = 'asc')
    {
        $this->add("sort", "geodist() $direction");

        return $this;
    }

    public function addDistanceToFieldList($name = '_dist_')
    {
        $this->fieldList("$name:geodist()");

        return $this;
    }
}
