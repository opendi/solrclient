<?php
namespace Opendi\Solr\Client;

class SolrFilter {

    private $filters = [];

    public function filterFor($term, $in, $cache = true) {
        $param = '';
        if (!$cache) {
            $param = '{!cache=false}';
        }

        $this->filters[] = $param.$in . ':' . $term;
        return $this;
    }

    public function get() {
        return (string)$this;
    }

    public function __toString() {
        $prefixed = [];
        foreach ($this->filters as $filter) {
            $prefixed[] = 'fq=' . $filter;
        }

        return implode('&', $prefixed);
    }
} 