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
namespace Opendi\Solr\Client\Tests\Query;

use Opendi\Solr\Client\Query\Select;
use Opendi\Solr\Client\Solr;

class SpatialTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testLocationSearch()
    {
        $field = "loc";
        $dist = "105.4";
        $lat = "45.816667";
        $lon = "15.983333";

        $actual = Solr::select()
            ->spatialField($field)
            ->centerPoint($lat, $lon)
            ->distance($dist)
            ->filterByDistance($field)
            ->sortByDistance()
            ->addDistanceToFieldList()
            ->render();

        $expected = "sfield=loc&pt=45.816667%2C15.983333&d=105.4&fq=%7B%21geofilt+sfield%3Dloc%7D&sort=geodist%28%29+asc&fl=_dist_%3Ageodist%28%29";
        $this->assertEquals($expected, $actual);
    }
}
