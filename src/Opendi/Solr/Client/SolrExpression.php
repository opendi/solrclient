<?php
namespace Opendi\Solr\Client;

class SolrExpression {

    protected $queryAnd = [];
    protected $queryOr = [];

    public function search($term, $in = null) {
        $value = '';
        if ($in != null) {
            $value .=  $in . ':' . $term;
        } else {
            $value = $term;
        }
        $this->queryAnd[] = $value;
        return $this;
    }

    public function andSearch($term, $in = null) {
        return $this->search($term, $in);
    }

    public function orSearch($term, $in = null) {
        $value = '';
        if ($in != null) {
            $value .=  $in . ':' . $term;
        } else {
            $value = $term;
        }
        $this->queryOr[] = $value;
        return $this;
    }

    public function get() {
        return $this->render();
    }

    private function render() {
        $result = implode('%20AND%20', $this->queryAnd);

        if (sizeOf($this->queryAnd) > 0 && sizeOf($this->queryOr) > 0) {
            $result .= '%20OR%20';
        }
        $result .= implode('%20OR%20', $this->queryOr);

        return $result;
    }
} 