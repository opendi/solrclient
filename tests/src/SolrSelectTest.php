<?php
/*
 *  Copyright 2014 Opendi Software AG
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing,
 *  software distributed under the License is distributed
 *  on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND,
 *  either express or implied. See the License for the specific
 *  language governing permissions and limitations under the License.
 */
namespace Opendi\Solr\Client\Tests;

use Opendi\Solr\Client\SolrDismaxParser;
use Opendi\Solr\Client\SolrExtendedDismaxParser;
use Opendi\Solr\Client\SolrFacet;
use Opendi\Solr\Client\SolrFilter;
use Opendi\Solr\Client\SolrSelect;

class SolrSelectTest extends \PHPUnit_Framework_TestCase
{
    public function testBasicSearch()
    {
        $select = new SolrSelect();
        $select->search('opendi', 'name');
        $this->assertEquals('q=name:opendi', $select->render());

        $select = new SolrSelect();
        $select->search('(opendi OR test)', 'name');
        $this->assertEquals('q=name:(opendi OR test)', $select->render());

        $select = new SolrSelect();
        $select->search('opendi', 'name')->andSearch('services', 'categories');
        $this->assertEquals('q=name:opendi%20AND%20categories:services', $select->render());

        $select = new SolrSelect();
        $select
            ->search('opendi', 'name')
            ->andSearch('services', 'categories')
            ->orSearch('localsearch', 'categories');
        $this->assertEquals('q=name:opendi%20AND%20categories:services%20OR%20categories:localsearch', $select->render());
    }

    public function testQueryFieldSearch()
    {
        $select = new SolrSelect();
        $select->search('opendi')->queryField('name');
        $this->assertEquals('q=opendi&qf=name', $select->render());
    }

    public function testIndent()
    {
        $select = new SolrSelect();
        $select->indent()->search('opendi', 'name');
        $this->assertEquals('q=name:opendi&indent=true', $select->render());
    }

    public function testRows()
    {
        $select = new SolrSelect();
        $select->indent()->rows(20)->search('opendi', 'name');
        $this->assertEquals('q=name:opendi&indent=true&rows=20', $select->render());

        $select = new SolrSelect();
        $select->indent()->rows(0)->search('opendi', 'name');
        $this->assertEquals('q=name:opendi&indent=true&rows=0', $select->render());

        $select = new SolrSelect();
        $select->indent()->search('opendi', 'name');
        $this->assertEquals('q=name:opendi&indent=true', $select->render());
    }

    public function testStart()
    {
        $select = new SolrSelect();
        $select->indent()->rows(20)->start(10)->search('opendi', 'name');
        $this->assertEquals('q=name:opendi&indent=true&rows=20&start=10', $select->render());
    }

    public function testFormat()
    {
        $select = new SolrSelect();
        $select->indent()->format(SolrSelect::FORMAT_JSON)->search('opendi', 'name');
        $this->assertEquals('q=name:opendi&wt=json&indent=true', $select->render());
        $select->format(SolrSelect::FORMAT_CSV);
        $this->assertEquals('q=name:opendi&wt=csv&indent=true', $select->render());
        $select->format(SolrSelect::FORMAT_PHP);
        $this->assertEquals('q=name:opendi&wt=php&indent=true', $select->render());
        $select->format(SolrSelect::FORMAT_RUBY);
        $this->assertEquals('q=name:opendi&wt=ruby&indent=true', $select->render());
        $select->format(SolrSelect::FORMAT_XML);
        $this->assertEquals('q=name:opendi&wt=xml&indent=true', $select->render());
    }

    public function testWithFilters()
    {
        $filter = new SolrFilter();
        $filter->filterFor('x','y',false);

        $select = new SolrSelect();
        $select
            ->search('opendi', 'name')
            ->filter($filter);

        $this->assertEquals('q=name:opendi&fq={!cache=false}y:x', $select->render());
    }

    public function testWithMultipleFilters()
    {
        $filter = new SolrFilter();
        $filter
            ->filterFor('x', 'y', false)
            ->filterFor('c', 'a');

        $select = new SolrSelect();
        $select
            ->search('opendi', 'name')
            ->format('json')
            ->filter($filter);

        $this->assertEquals('q=name:opendi&wt=json&fq={!cache=false}y:x&fq=a:c', $select->render());
    }

    public function testWithFacets()
    {
        $facet = new SolrFacet();
        $facet->field('category')->limit(1)->minCount(1);

        $select = new SolrSelect();
        $select
            ->search('opendi', 'name')
            ->facet($facet);

        $this->assertEquals('q=name:opendi&facet=true&facet.field=category&facet.limit=1&facet.mincount=1', $select->render());
    }

    public function testDebug()
    {
        $select = new SolrSelect();
        $select->search('opendi', 'name')->debug();
        $this->assertEquals('q=name:opendi&debug=true', $select->render());
    }

    public function testDismax()
    {
        $parser = new SolrDismaxParser();
        $select = new SolrSelect();
        $select->search('opendi', 'name')->parser($parser)->debug();
        $this->assertEquals('q=name:opendi&defType=dismax&debug=true', $select->render());
    }

    public function testExtendedDismax()
    {
        $parser = new SolrExtendedDismaxParser();
        $select = new SolrSelect();
        $select->search('opendi', 'name')->parser($parser)->debug();
        $this->assertEquals('q=name:opendi&defType=edismax&debug=true', $select->render());
    }
}
