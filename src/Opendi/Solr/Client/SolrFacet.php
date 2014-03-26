<?php
namespace Opendi\Solr\Client;

class SolrFacet {

    private $minCount = null;
    private $limit = null;
    private $fields = [];

    public function minCount($minCount) {
        $this->minCount = $minCount;
        return $this;
    }

    public function limit($limit) {
        $this->limit = $limit;
        return $this;
    }

    public function addField($field) {
        $this->fields[] = $field;
        return $this;
    }

    public function get() {
        if (sizeOf($this->fields) == 0) {
            throw new SolrException('Facets need at least on field to operate on');
        }
        return (string)$this;
    }

    public function __toString() {
        $result = 'facet=true';

        if ($this->minCount != null) {
            $result .= '&facet.mincount=' . $this->minCount;
        }

        if ($this->limit != null) {
            $result .= '&facet.limit=' . $this->limit;
        }

        $result .=  '&facet.field=' . implode('&facet.field=', $this->fields);
        return $result;
    }
} 