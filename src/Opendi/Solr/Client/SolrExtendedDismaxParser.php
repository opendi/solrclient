<?php
namespace Opendi\Solr\Client;

// TODO rest of supported fields
class SolrExtendedDismaxParser implements SolrParser {

    private $type = 'edismax';

    public function get() {
        return (string)$this;
    }

    public function __toString() {
        $result = 'defType=' . $this->type;

        return $result;
    }
} 