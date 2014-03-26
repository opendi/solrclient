<?php
namespace Opendi\Solr\Client;

class SolrSelectTest extends \PHPUnit_Framework_TestCase {

    public function testBasicSearch() {
        $select = new SolrSelect();
        $select->search('opendi', 'name');
        $this->assertEquals('q=name:opendi', $select->get());

        $select = new SolrSelect();
        $select->search('(opendi OR test)', 'name');
        $this->assertEquals('q=name:(opendi OR test)', $select->get());

        $select = new SolrSelect();
        $select->search('opendi', 'name')->andSearch('services', 'categories');
        $this->assertEquals('q=name:opendi%20AND%20categories:services', $select->get());

        $select = new SolrSelect();
        $select
            ->search('opendi', 'name')
            ->andSearch('services', 'categories')
            ->orSearch('localsearch', 'categories');
        $this->assertEquals('q=name:opendi%20AND%20categories:services%20OR%20categories:localsearch', $select->get());
    }

    public function testQueryFieldSearch() {
        $select = new SolrSelect();
        $select->search('opendi')->queryField('name');
        $this->assertEquals('q=opendi&qf=name', $select->get());
    }

    public function testIndent() {
        $select = new SolrSelect();
        $select->indent()->search('opendi', 'name');
        $this->assertEquals('q=name:opendi&indent=true', $select->get());
    }

    public function testRows() {
        $select = new SolrSelect();
        $select->indent()->rows(20)->search('opendi', 'name');
        $this->assertEquals('q=name:opendi&indent=true&rows=20', $select->get());

        $select = new SolrSelect();
        $select->indent()->rows(0)->search('opendi', 'name');
        $this->assertEquals('q=name:opendi&indent=true&rows=0', $select->get());

        $select = new SolrSelect();
        $select->indent()->search('opendi', 'name');
        $this->assertEquals('q=name:opendi&indent=true', $select->get());
    }

    public function testFormat() {
        $select = new SolrSelect();
        $select->indent()->format(SolrSelect::FORMAT_JSON)->search('opendi', 'name');
        $this->assertEquals('q=name:opendi&wt=json&indent=true', $select->get());
        $select->format(SolrSelect::FORMAT_CSV);
        $this->assertEquals('q=name:opendi&wt=csv&indent=true', $select->get());
        $select->format(SolrSelect::FORMAT_PHP);
        $this->assertEquals('q=name:opendi&wt=php&indent=true', $select->get());
        $select->format(SolrSelect::FORMAT_RUBY);
        $this->assertEquals('q=name:opendi&wt=ruby&indent=true', $select->get());
        $select->format(SolrSelect::FORMAT_XML);
        $this->assertEquals('q=name:opendi&wt=xml&indent=true', $select->get());
    }

    public function testWithFilters() {
        $filter = new SolrFilter();
        $filter->filterFor('x','y',false);

        $select = new SolrSelect();
        $select
            ->search('opendi', 'name')
            ->filter($filter);

        $this->assertEquals('q=name:opendi&fq={!cache=false}y:x', $select->get());
    }

    public function testWithMultipleFilters() {
        $filter = new SolrFilter();
        $filter
            ->filterFor('x', 'y', false)
            ->filterFor('c', 'a');

        $select = new SolrSelect();
        $select
            ->search('opendi', 'name')
            ->format('json')
            ->filter($filter);

        $this->assertEquals('q=name:opendi&wt=json&fq={!cache=false}y:x&fq=a:c', $select->get());
    }

    public function testWithFacets() {
        $facet = new SolrFacet();
        $facet->addField('category')->limit(1)->minCount(1);

        $select = new SolrSelect();
        $select
            ->search('opendi', 'name')
            ->facet($facet);

        $this->assertEquals('q=name:opendi&facet=true&facet.mincount=1&facet.limit=1&facet.field=category', $select->get());
    }

    public function testDebug() {
        $select = new SolrSelect();
        $select->search('opendi', 'name')->debug();
        $this->assertEquals('q=name:opendi&debug=true', $select->get());
    }

    public function testDismax() {
        $parser = new SolrDismaxParser();
        $select = new SolrSelect();
        $select->search('opendi', 'name')->parser($parser)->debug();
        $this->assertEquals('q=name:opendi&defType=dismax&debug=true', $select->get());
    }

    public function testExtendedDismax() {
        $parser = new SolrExtendedDismaxParser();
        $select = new SolrSelect();
        $select->search('opendi', 'name')->parser($parser)->debug();
        $this->assertEquals('q=name:opendi&defType=edismax&debug=true', $select->get());
    }
}
 