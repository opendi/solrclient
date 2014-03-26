<?php
namespace Opendi\Solr\Client;

class SolrExpressionTest extends \PHPUnit_Framework_TestCase {

    public function testBasicSearch() {
        $select = new SolrExpression();
        $select->search('opendi', 'name');
        $this->assertEquals('name:opendi', $select->get());

        $select = new SolrExpression();
        $select->search('(opendi OR test)', 'name');
        $this->assertEquals('name:(opendi OR test)', $select->get());

        $select = new SolrExpression();
        $select->search('opendi', 'name')->andSearch('services', 'categories');
        $this->assertEquals('name:opendi%20AND%20categories:services', $select->get());

        $select = new SolrSelect();
        $select
            ->search('opendi', 'name')
            ->andSearch('services', 'categories')
            ->orSearch('localsearch', 'categories');

        $expression = new SolrExpression();
        $expression->search('hello', 'world');
        $select->andExpression($expression);

        $this->assertEquals('q=name:opendi%20AND%20categories:services%20OR%20categories:localsearch%20AND%20(world:hello)', $select->get());

        $select = new SolrSelect();
        $select
            ->search('opendi', 'name')
            ->andSearch('services', 'categories')
            ->orSearch('localsearch', 'categories');

        $expression = new SolrExpression();
        $expression->search('hello', 'world')->andSearch('aaa', 'xxx');
        $select->andExpression($expression);

        $this->assertEquals('q=name:opendi%20AND%20categories:services%20OR%20categories:localsearch%20AND%20(world:hello%20AND%20xxx:aaa)', $select->get());

        $select = new SolrSelect();
        $select
            ->search('opendi', 'name')
            ->andSearch('services', 'categories')
            ->orSearch('localsearch', 'categories');

        $expression = new SolrExpression();
        $expression->search('hello', 'world')->andSearch('aaa', 'xxx');
        $select->andExpression($expression);

        $expression = new SolrExpression();
        $expression->search('123', '321');
        $select->orExpression($expression);

        $this->assertEquals('q=name:opendi%20AND%20categories:services%20OR%20categories:localsearch%20AND%20(world:hello%20AND%20xxx:aaa)%20OR%20(321:123)', $select->get());
    }
}
 