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
namespace Opendi\Solr\Client\Tests\Query;

use Opendi\Solr\Client\Query\Select;
use Opendi\Solr\Client\Query\Facet;
use Opendi\Solr\Client\Filter;
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
    }

    public function testIndent()
    {
        $select = new Select();
        $select->indent()->search('opendi', 'name');
        $this->assertEquals('indent=true&q=name%3Aopendi', $select->render());
    }

    public function testRows()
    {
        $select = new Select();
        $select->indent()->rows(20)->search('opendi', 'name');
        $this->assertEquals('indent=true&rows=20&q=name%3Aopendi', $select->render());

        $select = new Select();
        $select->indent()->rows(0)->search('opendi', 'name');
        $this->assertEquals('indent=true&rows=0&q=name%3Aopendi', $select->render());

        $select = new Select();
        $select->indent()->search('opendi', 'name');
        $this->assertEquals('indent=true&q=name%3Aopendi', $select->render());
    }

    public function testStart()
    {
        $select = new Select();
        $select->indent()->rows(20)->start(10)->search('opendi', 'name');
        $this->assertEquals('indent=true&rows=20&start=10&q=name%3Aopendi', $select->render());
    }

    public function testFormat()
    {
        $select = Solr::select()->indent()->format(Select::FORMAT_JSON)->search('opendi', 'name');
        $this->assertEquals('indent=true&wt=json&q=name%3Aopendi', $select->render());

        $select = Solr::select()->indent()->format(Select::FORMAT_CSV)->search('opendi', 'name');
        $this->assertEquals('indent=true&wt=csv&q=name%3Aopendi', $select->render());

        $select = Solr::select()->indent()->format(Select::FORMAT_PHP)->search('opendi', 'name');
        $this->assertEquals('indent=true&wt=php&q=name%3Aopendi', $select->render());

        $select = Solr::select()->indent()->format(Select::FORMAT_RUBY)->search('opendi', 'name');
        $this->assertEquals('indent=true&wt=ruby&q=name%3Aopendi', $select->render());

        $select = Solr::select()->indent()->format(Select::FORMAT_XML)->search('opendi', 'name');
        $this->assertEquals('indent=true&wt=xml&q=name%3Aopendi', $select->render());
    }

    public function testWithFilters()
    {
        $select = new Select();
        $select
            ->search('opendi', 'name')
            ->filter("y:x", ["cache" => false]);

        $this->assertEquals("q=name%3Aopendi&fq=%7B%21cache%3Dfalse%7Dy%3Ax", $select->render());

        $select = new Select();
        $select
            ->search('*', '*')
            ->filter('sqrt(popularity)', [
                "frange",
                "l" => 1,
                "u" => 4,
                "cache" => false
            ]);

        $this->assertEquals('q=%2A%3A%2A&fq=%7B%21frange+l%3D1+u%3D4+cache%3Dfalse%7Dsqrt%28popularity%29', $select->render());
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
        $this->assertEquals('q=name%3Aopendi&debug=all', $select->render());
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
