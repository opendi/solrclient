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

class SolrFilterTest extends \PHPUnit_Framework_TestCase {

    public function testBasicFilters() {
        $filter = new SolrFilter();
        $filter->filterFor('opendi', 'category');
        $this->assertEquals('fq=category:opendi', $filter->get());

        $filter = new SolrFilter();
        $filter->filterFor('opendi', 'category');
        $filter->filterFor('test', 'name');
        $this->assertEquals('fq=category:opendi&fq=name:test', $filter->get());

        $filter = new SolrFilter();
        $filter->filterFor('opendi', 'category');
        $filter->filterFor('test', 'name');
        $this->assertEquals('fq=category:opendi&fq=name:test', $filter->get());
    }

    public function testFilterCaching() {
        $filter = new SolrFilter();
        $filter->filterFor('opendi', 'category', false);
        $this->assertEquals('fq={!cache=false}category:opendi', $filter->get());

        $filter = new SolrFilter();
        $filter->filterFor('opendi', 'category');
        $filter->filterFor('test', 'name', false);
        $this->assertEquals('fq=category:opendi&fq={!cache=false}name:test', $filter->get());

        $filter = new SolrFilter();
        $filter->filterFor('opendi', 'category', false);
        $filter->filterFor('test', 'name');
        $this->assertEquals('fq={!cache=false}category:opendi&fq=name:test', $filter->get());

        $filter = new SolrFilter();
        $filter->filterFor('opendi', 'category', false);
        $filter->filterFor('test', 'name', false);
        $this->assertEquals('fq={!cache=false}category:opendi&fq={!cache=false}name:test', $filter->get());
    }

}
 