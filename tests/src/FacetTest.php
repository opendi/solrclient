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

use Opendi\Solr\Client\Facet;

class FacetTest extends \PHPUnit_Framework_TestCase
{
    public function testBasicFacet()
    {
        $filter = new Facet();
        $filter->field('category');
        $this->assertEquals('facet=true&facet.field=category', $filter->render());

        $filter = new Facet();
        $filter->field('category')->minCount(1)->limit(5);
        $this->assertEquals('facet=true&facet.field=category&facet.mincount=1&facet.limit=5', $filter->render());

        $filter = new Facet();
        $filter->field('category')->field('test');
        $this->assertEquals('facet=true&facet.field=category&facet.field=test', $filter->render());

        $filter = new Facet();
        $filter->field('category')->field('test')->prefix('A');
        $this->assertEquals('facet=true&facet.field=category&facet.field=test&facet.prefix=A', $filter->render());
    }

    public function testSort()
    {
        $actual = Facet::instance()->field('foo')->sort('index')->render();
        $expected = "facet=true&facet.field=foo&facet.sort=index";
        $this->assertSame($expected, $actual);

        $actual = Facet::instance()->field('foo')->sort('index', 'foo')->render();
        $expected = "facet=true&facet.field=foo&f.foo.facet.sort=index";
        $this->assertSame($expected, $actual);

        $actual = Facet::instance()->field('foo')->sortByIndex()->render();
        $expected = "facet=true&facet.field=foo&facet.sort=index";
        $this->assertSame($expected, $actual);

        $actual = Facet::instance()->field('foo')->sortByIndex('foo')->render();
        $expected = "facet=true&facet.field=foo&f.foo.facet.sort=index";
        $this->assertSame($expected, $actual);

        $actual = Facet::instance()->field('foo')->sortByCount()->render();
        $expected = "facet=true&facet.field=foo&facet.sort=count";
        $this->assertSame($expected, $actual);

        $actual = Facet::instance()->field('foo')->sortByCount('foo')->render();
        $expected = "facet=true&facet.field=foo&f.foo.facet.sort=count";
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException Opendi\Solr\Client\SolrException
     * @expectedExceptionMessage Invalid sort value "foo"
     */
    public function testSortInvalid()
    {
        Facet::instance()->sort('foo');
    }

    public function testLimit()
    {
        $actual = Facet::instance()->field('foo')->limit(10)->render();
        $expected = "facet=true&facet.field=foo&facet.limit=10";
        $this->assertSame($expected, $actual);

        $actual = Facet::instance()->field('foo')->limit(10, 'foo')->render();
        $expected = "facet=true&facet.field=foo&f.foo.facet.limit=10";
        $this->assertSame($expected, $actual);

        $actual = Facet::instance()->field('foo')->noLimit()->render();
        $expected = "facet=true&facet.field=foo&facet.limit=-1";
        $this->assertSame($expected, $actual);

        $actual = Facet::instance()->field('foo')->noLimit('foo')->render();
        $expected = "facet=true&facet.field=foo&f.foo.facet.limit=-1";
        $this->assertSame($expected, $actual);
    }

    public function testOffset()
    {
        $actual = Facet::instance()->field('foo')->offset(10)->render();
        $expected = "facet=true&facet.field=foo&facet.offset=10";
        $this->assertSame($expected, $actual);

        $actual = Facet::instance()->field('foo')->offset(10, 'foo')->render();
        $expected = "facet=true&facet.field=foo&f.foo.facet.offset=10";
        $this->assertSame($expected, $actual);
    }

    public function testPivot()
    {
        $actual = Facet::instance()->field('foo')->pivot('foo')->render();
        $expected = "facet=true&facet.field=foo&facet.pivot=foo";
        $this->assertSame($expected, $actual);

        $actual = Facet::instance()->field('foo')->pivot('foo','bar','baz')->render();
        $expected = "facet=true&facet.field=foo&facet.pivot=" . urlencode('foo,bar,baz');
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException Opendi\Solr\Client\SolrException
     * @expectedExceptionMessage At least one pivot field must be specified.
     */
    public function testPivotInvalid()
    {
        Facet::instance()->pivot();
    }
}
