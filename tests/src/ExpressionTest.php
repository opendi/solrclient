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

use Opendi\Solr\Client\Expression;
use Opendi\Solr\Client\Select;

class ExpressionTest extends \PHPUnit_Framework_TestCase
{
    public function testBasicSearch()
    {
        $select = new Expression();
        $select->search('opendi', 'name');
        $this->assertEquals('name:opendi', $select->render());

        $select = new Expression();
        $select->search('(opendi OR test)', 'name');
        $this->assertEquals('name:(opendi OR test)', $select->render());

        $select = new Expression();
        $select->search('opendi', 'name')->andSearch('services', 'categories');
        $this->assertEquals('name:opendi%20AND%20categories:services', $select->render());

        $select = new Select();
        $select
            ->search('opendi', 'name')
            ->andSearch('services', 'categories')
            ->orSearch('localsearch', 'categories');

        $expression = new Expression();
        $expression->search('hello', 'world');
        $select->andExpression($expression);

        $this->assertEquals('q=name:opendi%20AND%20categories:services%20OR%20categories:localsearch%20AND%20(world:hello)', $select->render());

        $select = new Select();
        $select
            ->search('opendi', 'name')
            ->andSearch('services', 'categories')
            ->orSearch('localsearch', 'categories');

        $expression = new Expression();
        $expression->search('hello', 'world')->andSearch('aaa', 'xxx');
        $select->andExpression($expression);

        $this->assertEquals('q=name:opendi%20AND%20categories:services%20OR%20categories:localsearch%20AND%20(world:hello%20AND%20xxx:aaa)', $select->render());

        $select = new Select();
        $select
            ->search('opendi', 'name')
            ->andSearch('services', 'categories')
            ->orSearch('localsearch', 'categories');

        $expression = new Expression();
        $expression->search('hello', 'world')->andSearch('aaa', 'xxx');
        $select->andExpression($expression);

        $expression = new Expression();
        $expression->search('123', '321');
        $select->orExpression($expression);

        $this->assertEquals('q=name:opendi%20AND%20categories:services%20OR%20categories:localsearch%20AND%20(world:hello%20AND%20xxx:aaa)%20OR%20(321:123)', $select->render());
    }
}
