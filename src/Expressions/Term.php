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

namespace Opendi\Solr\Client\Expressions;

/**
 * A search term in SOLR queries.
 */
class Term
{
    /** The field to search */
    protected $field;

    /** The value to look for */
    protected $value;

    /** The boost postfix, e.g. "^10" */
    protected $boost;

    /** The fuzzy postfix, e.g. "~" or "~0.2" */
    protected $fuzzy;

    /** A logical prefix, one of "!", "-", "+". */
    protected $prefix;

    public function __construct($value, $field = null)
    {
        $this->value = $value;
        $this->field = $field;
    }

    /** Sets the fuzzy option for this term with a given factor (optional) */
    public function fuzzy($factor = null)
    {
        if (isset($factor) && (!is_numeric($factor) || $factor < 0 || $factor > 1)) {
            throw new \InvalidArgumentException("Invalid fuzzyness factor: $factor.");
        }

        $this->fuzzy = "~$factor";

        return $this;
    }

    /** Sets a boost factor for this term. */
    public function boost($factor = null)
    {
        if (!is_numeric($factor) || $factor < 0) {
            throw new \InvalidArgumentException("Invalid boost factor: $factor.");
        }

        $this->boost = "^$factor";

        return $this;
    }

    /** Sets the term as required. */
    public function required()
    {
        $this->prefix = "+";

        return $this;
    }

    /** Sets the term as prohibited. */
    public function prohibited()
    {
        $this->prefix = "-";

        return $this;
    }

    /** Negates the term. */
    public function not()
    {
        $this->prefix = "!";

        return $this;
    }

    /** Renders the term to a string. */
    public function render()
    {
        $value = $this->value;

        // Encase in quotes if the value contains whitespace
        if (preg_match('/\\s/', $value)) {
            $value = "\"$value\"";
        }

        // Apply boost and fuzzy factors
        $value .= $this->fuzzy . $this->boost;

        // Add field name if provided
        if (isset($this->field)) {
            $value = implode(":", [$this->field, $value]);
        }

        // Apply the prefix
        $value = $this->prefix . $value;

        return $value;
    }

    public function __toString()
    {
        return $this->render();
    }
}
