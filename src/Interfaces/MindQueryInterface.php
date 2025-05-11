<?php

namespace Denason\Wikimind\Interfaces;

use Illuminate\Support\Collection;

/**
 * Interface MindQueryInterface
 *
 * Fluent interface for building and executing SPARQL queries
 * to retrieve structured data from Wikidata.
 *
 * This interface allows chaining methods to define SELECT variables,
 * WHERE clauses, OPTIONAL patterns, FILTERs, language preferences,
 * ordering, and limiting the result set.
 *
 * @package Denason\Wikimind\Interfaces
 *
 * @example
 * (new MindQuery())
 *     ->select(['item', 'itemLabel'])
 *     ->where('item', 'P31', 'Q4022')
 *     ->optional('item', 'rdfs:label', 'itemLabel')
 *     ->lang('fa')
 *     ->orderBy('itemLabel')
 *     ->limit(10)
 *     ->get('format'); // format -> array|json|collection ...
 */
interface MindQueryInterface
{
    /**
     * Enable or disable DISTINCT in the SELECT clause.
     *
     * @param bool $enabled Whether to apply DISTINCT to the query.
     * @return self
     */
    public function distinct(bool $enabled = true): self;

    /**
     * Define the variables to be selected in the SPARQL query.
     *
     * @param string[] $vars Array of variable names (without ? prefix).
     * @return self
     */
    public function select(array $vars): self;

    /**
     * Add a WHERE triple pattern.
     *
     * @param string $subject Subject (e.g., 'item').
     * @param string $predicate Predicate (e.g., 'P31' or 'wdt:P31').
     * @param string $object Object (e.g., 'Q4022').
     * @return self
     */

    public function where(string $subject, string $predicate, string $object): self;

    /**
     * Add an OPTIONAL triple pattern.
     *
     * @param string $subject
     * @param string $predicate
     * @param string $object
     * @return self
     */
    public function optional(string $subject, string $predicate, string $object): self;

    /**
     * Add a FILTER clause to the query.
     *
     * @param string $expression Valid SPARQL filter expression.
     * @return self
     */
    public function filter(string $expression): self;

    /**
     * Set the preferred language for labels and descriptions.
     *
     * @param string $lang Language code (e.g., 'en', 'fa').
     * @return self
     */
    public function lang(string $lang): self;

    /**
     * Set the maximum number of results to return.
     *
     * @param int $count
     * @return self
     */
    public function limit(int $count): self;

    /**
     * Sort the result set by a variable.
     *
     * @param string $var Variable name to sort by.
     * @param string $direction 'asc' or 'desc'.
     * @return self
     */
    public function orderBy(string $var, string $direction = 'asc'): self;

    /**
     * Execute the built SPARQL query and return results in a specific format.
     *
     * @param 'raw'|'array'|'json'|'Collection' $format The format of the result.
     * * @return array|string|collection
     */
    public function get(string $format = 'raw'): mixed;
}

