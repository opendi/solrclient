<?php
namespace Opendi\Solr\Client;

// TODO rest of supported fields
class SolrDismaxParser implements SolrParser {

    private $type = 'dismax';

    public function get() {
        return (string)$this;
    }

    public function __toString() {
        $result = 'defType=' . $this->type;

        return $result;
    }
} 