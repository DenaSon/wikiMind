<?php

namespace Denason\Wikimind\Fluents;

use Denason\Wikimind\Interfaces\MindQueryInterface;
use Denason\Wikimind\Actions\ExecuteMindQueryAction;

/**
 * Class MindQuery
 *
 * Fluent builder for constructing and executing SPARQL queries on Wikidata.
 *
 * @package Denason\Wikimind\Fluents
 */
class MindQuery implements MindQueryInterface
{
    protected array $select = [];
    protected array $wheres = [];
    protected array $optionals = [];
    protected array $filters = [];
    protected string $lang = 'en';
    protected int $limit = 10;
    protected ?string $orderBy = null;
    protected bool $distinct = false;


    public function distinct(bool $value = true): self
    {
        $this->distinct = $value;
        return $this;
    }

    protected string $orderDir = 'ASC';
    /**
     * @inheritdoc
     */
    public function select(array $vars): self
    {
        $this->select = $vars;
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function where(string $subject, string $predicate, string $object): self
    {
        $this->wheres[] = [$subject, $predicate, $object];
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function optional(string $subject, string $predicate, string $object): self
    {
        $this->optionals[] = [$subject, $predicate, $object];
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function filter(string $expression): self
    {
        $this->filters[] = $expression;
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function lang(string $lang): self
    {
        $this->lang = $lang;
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function limit(int $count): self
    {
        $this->limit = $count;
        return $this;
    }
    /**
     * @inheritdoc
     */
    public function orderBy(string $var, string $direction = 'asc'): self
    {
        $this->orderBy = $var;
        $this->orderDir = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        return $this;
    }



    /**
     * @inheritdoc
     */
    public function get(string $format = 'raw'): mixed
    {
        $results = (new ExecuteMindQueryAction())->execute(
            $this->select,
            $this->wheres,
            $this->optionals,
            $this->filters,
            $this->lang,
            $this->limit,
            $this->orderBy,
            $this->orderDir,
            $this->distinct
        );

        return $this->formatResults($results, $format);
    }


    protected function formatResults(array $results, string $format): mixed
    {
        $bindings = $results['results']['bindings'] ?? [];

        $mapped = array_map(function ($row) {
            return array_map(fn($v) => $v['value'] ?? '', $row);
        }, $bindings);

        return match ($format) {
            'array' => $mapped,
            'json' => json_encode($mapped, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            'collection' => collect($bindings)->map(function ($row) {
                return (object) collect($row)->mapWithKeys(fn($v, $k) => [$k => $v['value'] ?? ''])->all();
            }),
            default => $bindings,
        };
    }


}
