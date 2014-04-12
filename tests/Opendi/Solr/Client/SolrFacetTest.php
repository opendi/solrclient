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

class SolrFacetTest extends \PHPUnit_Framework_TestCase {
    public function testBasicFacet() {
        $filter = new SolrFacet();
        $filter->addField('category');
        $this->assertEquals('facet=true&facet.field=category', $filter->get());

        $filter = new SolrFacet();
        $filter->addField('category')->minCount(1)->limit(5);
        $this->assertEquals('facet=true&facet.mincount=1&facet.limit=5&facet.field=category', $filter->get());

        $filter = new SolrFacet();
        $filter->addField('category')->addField('test');
        $this->assertEquals('facet=true&facet.field=category&facet.field=test', $filter->get());

        $filter = new SolrFacet();
        $filter->addField('category')->addField('test')->prefix('A');
        $this->assertEquals('facet=true&facet.prefix=A&facet.field=category&facet.field=test', $filter->get());
    }

}
 