<?php
namespace Opendi\Solr\Client;

class SolrDismaxParserTest extends \PHPUnit_Framework_TestCase {

    public function testDismaxBasic() {
        $parser  = new SolrDismaxParser();
        $this->assertEquals('defType=dismax', $parser->get());
    }
}
 