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

use Opendi\Solr\Client\Query\Group;
use Opendi\Solr\Client\Solr;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    public function testBasicGroup()
    {
        $field = "foo";

        $group = new Group();
        $group->field($field);
        $this->assertEquals("group=true&group.field=$field", $group->render());

        $group = new Group();
        $group->field($field)->format(Group::FORMAT_GROUPED);
        $this->assertEquals("group=true&group.field=$field&group.format=grouped", $group->render());

        $group = new Group();
        $group->field($field)->main();
        $this->assertEquals("group=true&group.field=$field&group.format=simple&group.main=true", $group->render());

        $group = new Group();
        $group->field($field)->ngroups();
        $this->assertEquals("group=true&group.field=$field&group.ngroups=true", $group->render());

        $group = new Group();
        $group->field($field)->truncate();
        $this->assertEquals("group=true&group.field=$field&group.truncate=true", $group->render());

        $group = new Group();
        $group->field($field)->facet();
        $this->assertEquals("group=true&group.field=$field&group.facet=true", $group->render());

        $percent = "10";
        $group = new Group();
        $group->field($field)->cache($percent);
        $this->assertEquals("group=true&group.field=$field&group.cache.percent=$percent", $group->render());
    }

    public function testSort()
    {
        $actual = Solr::group()->field('foo')->sort('foo desc')->render();
        $expected = "group=true&group.field=foo&group.sort=foo+desc";
        $this->assertSame($expected, $actual);
    }

    /**
     * @expectedException \Opendi\Solr\Client\SolrException
     * @expectedExceptionMessage Invalid group format
     */
    public function testFormatInvalid()
    {
        Solr::group()->format('foo');
    }

    public function testLimit()
    {
        $actual = Solr::group()->field('foo')->limit(10)->render();
        $expected = "group=true&group.field=foo&group.limit=10";
        $this->assertSame($expected, $actual);
    }

    public function testOffset()
    {
        $actual = Solr::group()->field('foo')->offset(10)->render();
        $expected = "group=true&group.field=foo&group.offset=10";
        $this->assertSame($expected, $actual);
    }

    public function testMergeToSelect()
    {
        $group = Solr::group()->field('foo');
        $select = Solr::select()->group($group);

        $expected = 'group=true&group.field=foo';
        $this->assertSame($expected, $group->render());
        $this->assertSame($expected, $select->render());
    }
}
