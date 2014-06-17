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
namespace Opendi\Solr\Client\Tests;

use Opendi\Solr\Client\Filter;
use Opendi\Solr\Client\Solr;

class FilterTest extends \PHPUnit_Framework_TestCase
{
    public function testBasicFilters()
    {
        $filter = new Filter();
        $filter->filterFor('opendi', 'category');
        $this->assertEquals('fq=category:opendi', $filter->render());

        $filter = new Filter();
        $filter->filterFor('opendi', 'category');
        $filter->filterFor('test', 'name');
        $this->assertEquals('fq=category:opendi&fq=name:test', $filter->render());

        $filter = new Filter();
        $filter->filterFor('opendi', 'category');
        $filter->filterFor('test', 'name');
        $this->assertEquals('fq=category:opendi&fq=name:test', $filter->render());
    }

    public function testFilterCaching()
    {
        $filter = new Filter();
        $filter->filterFor('opendi', 'category', false);
        $this->assertEquals('fq={!cache=false}category:opendi', $filter->render());

        $filter = new Filter();
        $filter->filterFor('opendi', 'category');
        $filter->filterFor('test', 'name', false);
        $this->assertEquals('fq=category:opendi&fq={!cache=false}name:test', $filter->render());

        $filter = new Filter();
        $filter->filterFor('opendi', 'category', false);
        $filter->filterFor('test', 'name');
        $this->assertEquals('fq={!cache=false}category:opendi&fq=name:test', $filter->render());

        $filter = new Filter();
        $filter->filterFor('opendi', 'category', false);
        $filter->filterFor('test', 'name', false);
        $this->assertEquals('fq={!cache=false}category:opendi&fq={!cache=false}name:test', $filter->render());
    }

    public function testFactory()
    {
        $filter1 = Solr::filter();
        $filter2 = Solr::filter();

        $this->assertNotSame($filter1, $filter2);
        $this->assertInstanceOf(Filter::class, $filter1);
        $this->assertInstanceOf(Filter::class, $filter2);
    }
}
