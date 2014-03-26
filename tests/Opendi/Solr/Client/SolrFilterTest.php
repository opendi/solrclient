<?php
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
 