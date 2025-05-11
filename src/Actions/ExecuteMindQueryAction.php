<?php

namespace Denason\Wikimind\Actions;

use Denason\Wikimind\Traits\WikimindBaseTrait;

/**
 * Class ExecuteMindQueryAction
 *
 * Constructs and executes a custom SPARQL query based on the fluent builder inputs.
 */
class ExecuteMindQueryAction
{
    use WikimindBaseTrait;

    public function __construct()
    {
        $this->initWikimindConfig();
    }




    /**
     * Build and run the SPARQL query.
     *
     * @param array $select
     * @param array $wheres
     * @param array $optionals
     * @param array $filters
     * @param string $lang
     * @param int $limit
     * @param string|null $orderBy
     * @param string $orderDir
     * @return array
     */
    public function execute(
        array $select,
        array $wheres,
        array $optionals,
        array $filters,
        string $lang,
        int $limit,
        ?string $orderBy,
        string $orderDir,
        bool $distinct = false

    ): array {
        $sparql = $this->buildQuery(
            $select, $wheres, $optionals, $filters,
            $lang, $limit, $orderBy, $orderDir, $distinct
        );



        return $this->runSparqlQuery($sparql);
    }

    /**
     * Build the full SPARQL query string.
     */
    protected function buildQuery(
        array $select,
        array $wheres,
        array $optionals,
        array $filters,
        string $lang,
        int $limit,
        ?string $orderBy,
        string $orderDir,
        bool $distinct = false
    ): string {
        $selectClause = ($distinct ? 'SELECT DISTINCT ' : 'SELECT ') . implode(' ', array_map(function ($v) {
                return '?' . $v;
            }, $select ?: ['*']));


        $whereParts = [];

        foreach ($wheres as [$s, $p, $o]) {
            $whereParts[] = $this->triple($s, $p, $o);
        }

        foreach ($optionals as [$s, $p, $o]) {
            $whereParts[] = 'OPTIONAL { ' . $this->triple($s, $p, $o) . ' }';
        }

        foreach ($filters as $filter) {
            $whereParts[] = 'FILTER(' . $filter . ')';
        }

        $whereParts[] = 'SERVICE wikibase:label { bd:serviceParam wikibase:language "' . $lang . ',en" . }';

        $query = $selectClause . " WHERE {\n" . implode("\n", $whereParts) . "\n}";

        if ($orderBy) {
            $query .= "\nORDER BY " . strtoupper($orderDir) . '(?' . $orderBy . ')';
        }

        $query .= "\nLIMIT " . $limit;

        return $query;
    }

    protected function triple(string $s, string $p, string $o): string
    {
        return '?' . $s . ' ' . $this->normalizePredicate($p) . ' ' . $this->normalizeObject($o) . ' .';
    }

    protected function normalizePredicate(string $p): string
    {
        return preg_match('/^P\d+$/', $p) ? 'wdt:' . $p : $p;
    }

    protected function normalizeObject(string $o): string
    {
        return preg_match('/^Q\d+$/', $o) ? 'wd:' . $o : '?' . $o;
    }




}
