<?php
namespace Opendi\Solr\Client;

class SolrExtendedDismaxParserTest extends \PHPUnit_Framework_TestCase {

    public function testDismaxBasic() {
        $parser  = new SolrExtendedDismaxParser();
        $this->assertEquals('defType=edismax', $parser->get());
    }
}
 