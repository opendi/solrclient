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

use Opendi\Solr\Client\Parsers\DismaxParser;
use Opendi\Solr\Client\Parsers\ExtendedDismaxParser;
use Opendi\Solr\Client\Facet;
use Opendi\Solr\Client\Filter;
use Opendi\Solr\Client\Select;
use Opendi\Solr\Client\Solr;

class SelectTest extends \PHPUnit_Framework_TestCase
{
    public function testBasicSearch()
    {
        $select = new Select();
        $select->search('opendi', 'name');
        $this->assertEquals('q=name%3Aopendi', $select->render());

        $select = new Select();
        $select->search('(opendi OR test)', 'name');
        $this->assertEquals('q=' . urlencode('name:(opendi OR test)'), $select->render());

        $select = new Select();
        $select->search('opendi', 'name')->andSearch('services', 'categories');
        $this->assertEquals('q=' . urlencode('name:opendi AND categories:services'), $select->render());

        $select = new Select();
        $select
            ->search('opendi', 'name')
            ->andSearch('services', 'categories')
            ->orSearch('localsearch', 'categories');
        $this->assertEquals('q=' . urlencode('name:opendi AND categories:services OR categories:localsearch'), $select->render());
    }

    public function testQueryFieldSearch()
    {
        $select = new Select();
        $select->search('opendi')->queryField('name');
        $this->assertEquals('q=opendi&qf=name', $select->render());
    }

    public function testIndent()
    {
        $select = new Select();
        $select->indent()->search('opendi', 'name');
        $this->assertEquals('q=name%3Aopendi&indent=true', $select->render());
    }

    public function testRows()
    {
        $select = new Select();
        $select->indent()->rows(20)->search('opendi', 'name');
        $this->assertEquals('q=name%3Aopendi&indent=true&rows=20', $select->render());

        $select = new Select();
        $select->indent()->rows(0)->search('opendi', 'name');
        $this->assertEquals('q=name%3Aopendi&indent=true&rows=0', $select->render());

        $select = new Select();
        $select->indent()->search('opendi', 'name');
        $this->assertEquals('q=name%3Aopendi&indent=true', $select->render());
    }

    public function testStart()
    {
        $select = new Select();
        $select->indent()->rows(20)->start(10)->search('opendi', 'name');
        $this->assertEquals('q=name%3Aopendi&indent=true&rows=20&start=10', $select->render());
    }

    public function testFormat()
    {
        $select = new Select();
        $select->indent()->format(Select::FORMAT_JSON)->search('opendi', 'name');
        $this->assertEquals('q=name%3Aopendi&wt=json&indent=true', $select->render());
        $select->format(Select::FORMAT_CSV);
        $this->assertEquals('q=name%3Aopendi&wt=csv&indent=true', $select->render());
        $select->format(Select::FORMAT_PHP);
        $this->assertEquals('q=name%3Aopendi&wt=php&indent=true', $select->render());
        $select->format(Select::FORMAT_RUBY);
        $this->assertEquals('q=name%3Aopendi&wt=ruby&indent=true', $select->render());
        $select->format(Select::FORMAT_XML);
        $this->assertEquals('q=name%3Aopendi&wt=xml&indent=true', $select->render());
    }

    public function testWithFilters()
    {
        $filter = new Filter();
        $filter->filterFor('x','y',false);

        $select = new Select();
        $select
            ->search('opendi', 'name')
            ->filter($filter);

        $this->assertEquals('q=name%3Aopendi&fq={!cache=false}y:x', $select->render());
    }

    public function testWithMultipleFilters()
    {
        $filter = new Filter();
        $filter
            ->filterFor('x', 'y', false)
            ->filterFor('c', 'a');

        $select = new Select();
        $select
            ->search('opendi', 'name')
            ->format('json')
            ->filter($filter);

        $this->assertEquals('q=name%3Aopendi&wt=json&fq={!cache=false}y:x&fq=a:c', $select->render());
    }

    public function testWithFacets()
    {
        $facet = new Facet();
        $facet->field('category')->limit(1)->minCount(1);

        $select = new Select();
        $select
            ->search('opendi', 'name')
            ->facet($facet);

        $this->assertEquals('q=name%3Aopendi&facet=true&facet.field=category&facet.limit=1&facet.mincount=1', $select->render());
    }

    public function testDebug()
    {
        $select = new Select();
        $select->search('opendi', 'name')->debug();
        $this->assertEquals('q=name%3Aopendi&debug=true', $select->render());
    }

    public function testDismax()
    {
        $parser = new DismaxParser();
        $select = new Select();
        $select->search('opendi', 'name')->parser($parser)->debug();
        $this->assertEquals('q=name%3Aopendi&defType=dismax&debug=true', $select->render());
    }

    public function testExtendedDismax()
    {
        $parser = new ExtendedDismaxParser();
        $select = new Select();
        $select->search('opendi', 'name')->parser($parser)->debug();
        $this->assertEquals('q=name%3Aopendi&defType=edismax&debug=true', $select->render());
    }

    public function testFactory()
    {
        $select1 = Solr::select();
        $select2 = Solr::select();

        $this->assertNotSame($select1, $select2);
        $this->assertInstanceOf(Select::class, $select1);
        $this->assertInstanceOf(Select::class, $select2);
    }
}
