<?php
namespace Opendi\Solr\Client;

class SolrFacetTest extends \PHPUnit_Framework_TestCase {
    public function testBasicFacet() {
        $filter = new SolrFacet();
        $filter->addField('category');
        $this->assertEquals('facet=true&facet.field=category', $filter->get());

        $filter = new SolrFacet();
        $filter->addField('category')->minCount(1)->limit(5);
        $this->assertEquals('facet=true&facet.mincount=1&facet.limit=5&facet.field=category', $filter->get());

        $filter = new SolrFacet();
        $filter->addField('category')->addField('test');
        $this->assertEquals('facet=true&facet.field=category&facet.field=test', $filter->get());
    }

}
 