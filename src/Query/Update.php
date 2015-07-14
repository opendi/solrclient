<?php
/*
 *  Copyright 2015 Opendi Software AG
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
namespace Opendi\Solr\Client\Query;

use Opendi\Solr\Client\Query;

class Update extends Query
{
    private $body;

    private $contentType = 'application/json';

    const CONTENT_TYPE_JSON = 'application/json';
    const CONTENT_TYPE_XML = 'application/xml';

    // -- Query elements -------------------------------------------------------

    /**
     * Issues a commit after the data has been ingested.
     *
     * @param boolean $commit
     *
     * @return Update
     */
    public function commit($commit = true)
    {
        $this->add('commit', $commit);

        return $this;
    }

    /**
     * Optimize the core after data has been ingested. May take some time.
     *
     * @param boolean $optimize
     *
     * @return Update
     */
    public function optimize($optimize = true)
    {
        $this->add('optimize', $optimize);

        return $this;
    }

    /**
     * If true (the default), check for and overwrite duplicate documents, based
     * on the uniqueKey field declared in the Solr schema.
     *
     * If you know the documents you are indexing do not contain any duplicates
     * then you may see a considerable speed up setting this to false.
     *
     * WARNING: Setting overwrite to FALSE disables checking for duplicates and
     * will cause duplicates to be inserted into the index.
     *
     * @param boolean $overwrite
     *
     * @return Update
     */
    public function overwrite($overwrite = true)
    {
        $this->add('overwrite', $overwrite);

        return $this;
    }

    /**
     * Add the document within the specified number of milliseconds.
     *
     * @param  integer $commitWithin
     *
     * @return Update
     */
    public function commitWithin($commitWithin)
    {
        $this->add('commitWithin', $commitWithin);

        return $this;
    }

    /**
     * Specifies the Response Writer to be used to format the query response.
     *
     * @see https://cwiki.apache.org/confluence/display/solr/Response+Writers
     *
     * @param  string $format The response writer to use.
     *
     * @return Select
     */
    public function format($format)
    {
        $this->add('wt', $format);

        return $this;
    }

    // -- Accessors ------------------------------------------------------------

    /**
     * Sets the body.
     *
     * @param  mixed $body Body contents or stream.
     *
     * @return Update
     */
    public function body($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Returns the update message body.
     *
     * @return mixed Body contents or stream.
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sets the update content type used in the header.
     *
     * @param  string $contentType
     *
     * @return Update
     */
    public function contentType($contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }

    /**
     * Returns the content type header value.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }
}
