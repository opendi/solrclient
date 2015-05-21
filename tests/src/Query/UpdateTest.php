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

use Opendi\Solr\Client\Query\Update;
use Opendi\Solr\Client\Solr;

class UpdateTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $update1 = Solr::update();
        $update2 = Solr::update();

        $this->assertNotSame($update1, $update2);
        $this->assertInstanceOf(Update::class, $update1);
        $this->assertInstanceOf(Update::class, $update2);
    }

    public function testQueryParams1()
    {
        $update = new Update();
        $update->commit();
        $update->optimize();
        $update->overwrite();
        $update->commitWithin(10);

        $expected = "commit=true&optimize=true&overwrite=true&commitWithin=10";
        $actual = $update->render();

        $this->assertSame($expected, $actual);
    }

    public function testBody()
    {
        $body = "foo";
        $ct = "bar";

        $update = new Update();
        $update->body($body)->contentType($ct);

        $this->assertSame($body, $update->getBody());
        $this->assertSame($ct, $update->getContentType());
    }
}
