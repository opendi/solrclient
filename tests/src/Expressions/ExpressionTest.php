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
namespace Opendi\Solr\Client\Tests\Expressions;

use Opendi\Solr\Client\Expressions\Expression as e;
use Opendi\Solr\Client\Expressions\Term as t;

class ExpressionTest extends \PHPUnit_Framework_TestCase
{
    public function testSimple()
    {
        $terms = ['foo', 'bar', 'baz'];

        $exp = e::noop();

        $this->assertInstanceOf(e::class, $exp);
        $this->assertSame(e::OP_NOOP, $exp->getOperation());
        $this->assertEmpty($exp->getTerms());
        $this->assertEmpty($exp->getLocals());
        $this->assertEmpty($exp->render());

        $exp = e::all();

        $this->assertInstanceOf(e::class, $exp);
        $this->assertSame(e::OP_AND, $exp->getOperation());
        $this->assertEmpty($exp->getTerms());
        $this->assertEmpty($exp->getLocals());
        $this->assertEmpty($exp->render());

        $exp = e::either();

        $this->assertInstanceOf(e::class, $exp);
        $this->assertSame(e::OP_OR, $exp->getOperation());
        $this->assertEmpty($exp->getTerms());
        $this->assertEmpty($exp->getLocals());
        $this->assertEmpty($exp->render());

        $exp = e::noop('foo', 'bar', 'baz');

        $this->assertInstanceOf(e::class, $exp);
        $this->assertSame(e::OP_NOOP, $exp->getOperation());
        $this->assertSame($terms, $exp->getTerms());
        $this->assertEmpty($exp->getLocals());
        $this->assertSame("foo bar baz", $exp->render());
        $this->assertSame("foo bar baz", strval($exp));

        $exp = e::all('foo', 'bar', 'baz');

        $this->assertInstanceOf(e::class, $exp);
        $this->assertSame(e::OP_AND, $exp->getOperation());
        $this->assertSame($terms, $exp->getTerms());
        $this->assertEmpty($exp->getLocals());
        $this->assertSame("foo AND bar AND baz", $exp->render());
        $this->assertSame("foo AND bar AND baz", strval($exp));

        $exp = e::either('foo', 'bar', 'baz');

        $this->assertInstanceOf(e::class, $exp);
        $this->assertSame(e::OP_OR, $exp->getOperation());
        $this->assertSame($terms, $exp->getTerms());
        $this->assertEmpty($exp->getLocals());
        $this->assertSame("foo OR bar OR baz", $exp->render());
        $this->assertSame("foo OR bar OR baz", strval($exp));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid operation: foo
     */
    public function testInvalidOp()
    {
        $exp = new e("foo");
    }

    public function testAdd()
    {
        $terms = ["foo", "bar", "baz"];

        $exp = e::all();

        foreach ($terms as $term) {
            $exp->add($term);
        }

        $this->assertSame($terms, $exp->getTerms());
        $this->assertEmpty($exp->getLocals());
    }

    public function testLocals()
    {
        $locals = ["geofilt", "cache" => false];

        $exp = e::noop()->locals($locals);

        $this->assertSame($locals, $exp->getLocals());
        $this->assertSame("geofilt cache=false", $exp->renderLocals());
        $this->assertSame("{!geofilt cache=false}", $exp->render());
    }

    public function testNestedTerms()
    {
        $t1 = e::term("v1")->fuzzy();
        $t2 = e::term("v2")->fuzzy(0.2);
        $t3 = e::term("v3", "f3")->boost(3);
        $t4 = e::term("v4", "f4");
        $t5 = e::term("v5")->required();
        $t6 = e::term("v6")->prohibited();
        $t7 = e::term("v7")->not();

        $this->assertSame("v1~", $t1->render());
        $this->assertSame("v2~0.2", $t2->render());
        $this->assertSame("f3:v3^3", $t3->render());
        $this->assertSame("f4:v4", $t4->render());
        $this->assertSame("+v5", $t5->render());
        $this->assertSame("-v6", $t6->render());
        $this->assertSame("!v7", $t7->render());

        $this->assertSame("v1~", strval($t1));
        $this->assertSame("v2~0.2", strval($t2));
        $this->assertSame("f3:v3^3", strval($t3));
        $this->assertSame("f4:v4", strval($t4));
        $this->assertSame("+v5", strval($t5));
        $this->assertSame("-v6", strval($t6));
        $this->assertSame("!v7", strval($t7));

        $exp = e::either(
            e::all($t1, $t2),
            e::all($t3, $t4),
            e::all($t5, $t6, $t7)
        );

        $expected = "(v1~ AND v2~0.2) OR (f3:v3^3 AND f4:v4) OR (+v5 AND -v6 AND !v7)";
        $this->assertSame($expected, $exp->render());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid fuzzyness factor: bar
     */
    public function testInvalidFuzzy()
    {
        $exp = e::term("foo")->fuzzy("bar");
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid boost factor: bar
     */
    public function testInvalidBoost()
    {
        $exp = e::term("foo")->boost("bar");
    }
}
