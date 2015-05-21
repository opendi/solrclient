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

class Expression
{
    /** No operation, terms are joined with a space. */
    const OP_NOOP = " ";

    /** Boolean AND operation. */
    const OP_AND = " AND ";

    /** Boolean OR operation. */
    const OP_OR = " OR ";

    protected $ops = [
        self::OP_AND,
        self::OP_OR,
        self::OP_NOOP
    ];

    /** Operation used to join the terms. */
    protected $op;

    /**
     * The terms within this expression.
     *
     * @var array
     */
    protected $terms = [];

    /**
     * Local variables.
     *
     * @see https://cwiki.apache.org/confluence/display/solr/Local+Parameters+in+Queries
     *
     * @var array
     */
    protected $locals = [];

    public function __construct($op = self::OP_NOOP)
    {
        if (!in_array($op, $this->ops)) {
            throw new \InvalidArgumentException("Invalid operation: $op");
        }
        $this->op = $op;
    }

    /**
     * Adds a new term to the expression.
     *
     * @param Term|string $term
     */
    public function add($term)
    {
        $this->terms[] = $term;

        return $this;
    }

    /**
     * Sets an array of local parameters.
     *
     * @param array
     */
    public function locals(array $locals)
    {
        $this->locals = $locals;

        return $this;
    }

    /**
     * Renders the expression to a string.
     *
     * @return string
     */
    public function render()
    {
        // Render any sub-terms to strings
        $renderer = function ($item) {
            if ($item instanceof Expression) {
                return "(" . $item->render() . ")";
            }

            if ($item instanceof Term) {
                return $item->render();
            }

            return $item;
        };

        $terms = array_map($renderer, $this->terms);

        // Join by operation
        $terms = implode($this->op, $terms);

        $locals = $this->renderLocals();
        if (!empty($locals)) {
            $terms = "{!$locals}$terms";
        }

        return $terms;
    }

    /**
     * Renders local parameters to a string.
     *
     * @see https://cwiki.apache.org/confluence/display/solr/Local+Parameters+in+Queries
     *
     * @return string
     */
    public function renderLocals()
    {
        $join = function ($key, $value) {
            if (is_bool($value)) {
                $value = $value ? "true" : "false";
            }

            return is_integer($key) ? $value : "$key=$value";
        };

        return implode(" ", array_map(
            $join,
            array_keys($this->locals),
            array_values($this->locals)
        ));
    }

    public function __toString()
    {
        return $this->render();
    }

    // -- Accessors ------------------------------------------------------------


    public function getTerms()
    {
        return $this->terms;
    }

    public function getOperation()
    {
        return $this->op;
    }

    public function getLocals()
    {
        return $this->locals;
    }

    // -- Static factory methods -----------------------------------------------

    /**
     * Factory method, returns a new expression with no operation.
     *
     * Operations will be joined by a space, and this will use the default
     * specified by query, or Solr configuration.
     *
     * @return Expression
     */
    public static function noop()
    {
        return self::factory(self::OP_NOOP, func_get_args());
    }

    /**
     * Factory method, returns a new expression with AND operation.
     *
     * @return Expression
     */
    public static function all()
    {
        return self::factory(self::OP_AND, func_get_args());
    }

    /**
     * Factory method, returns a new expression with OR operation.
     *
     * @return Expression
     */
    public static function either()
    {
        return self::factory(self::OP_OR, func_get_args());
    }

    /**
     * Generic Expression factory method.
     *
     * @param  string  $op    Operation.
     * @param  Term[]  $terms An array of Terms.
     *
     * @return Expression
     */
    public static function factory($op, array $terms)
    {
        $ex = new self($op);

        foreach ($terms as $term) {
            $ex->add($term);
        }

        return $ex;
    }

    /**
     * Factory method, returns a new Term.
     *
     * @param  string $value Value to search for.
     * @param  string $field Field to serach in (optional).
     *
     * @return Term
     */
    public static function term($value, $field = null)
    {
        return new Term($value, $field);
    }
}
