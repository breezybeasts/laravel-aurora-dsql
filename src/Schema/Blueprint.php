<?php

namespace BreezyBeasts\AuroraDsql\Schema;

use http\Exception\RuntimeException;

class Blueprint extends \Illuminate\Database\Schema\Blueprint
{
    protected array $primaryKeys = [];

    protected array $uniqueKeys = [];

    /**
     * Create a new UUID column on the table.
     *
     * @param  string  $column
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function id($column = 'id')
    {
        return match (config('auora_dsql.migrations.id')) {
            'uuid' => parent::uuid($column),
            'ulid' => parent::ulid($column),
            default => parent::id($column),
        };
    }

    public function increments($column)
    {
        throw new RuntimeException('Auto increments are not supported.');
    }

    public function bigIncrements($column)
    {
        throw new RuntimeException('Auto increments are not supported.');
    }

    public function mediumIncrements($column)
    {
        throw new RuntimeException('Auto increments are not supported.');
    }

    public function smallIncrements($column)
    {
        throw new RuntimeException('Auto increments are not supported.');
    }

    public function tinyIncrements($column)
    {
        throw new RuntimeException('Auto increments are not supported.');
    }

    public function integerIncrements($column)
    {
        throw new RuntimeException('Auto increments are not supported.');
    }

    public function asyncIndex($columns, $indexName = null)
    {
        $indexName = $indexName ?: $this->createIndexName('index', (array) $columns);

        return $this->addCommand('asyncIndex', [
            'columns' => (array) $columns,
            'ifNotExists' => false,
            'unique' => false,
            'indexName' => $indexName,
            'includedColumns' => [],
            'nullsPosition' => null,
        ]);

    }

    protected function addCommand($name, array $parameters = [])
    {
        if ($name == 'asyncIndex') {
            $command = new AsyncIndexCommand(array_merge(compact('name'), $parameters));

            $this->commands[] = $command;

            return $command;
        }

        return parent::addCommand($name, $parameters);
    }

    public function primary($columns = null, $name = null, $algorithm = null): static
    {
        $columns = (array) $columns;

        // Track primary keys for compilation into CREATE TABLE
        $this->primaryKeys = array_merge($this->primaryKeys, $columns);

        return $this;
    }

    public function getPrimaryKeys(): array
    {
        return $this->primaryKeys;
    }

    public function unique($columns, $name = null, $algorithm = null): static
    {
        $columns = (array) $columns;

        // Track unique keys for compilation into CREATE TABLE
        $this->uniqueKeys[] = [
            'columns' => $columns,
            'name' => $name,
        ];

        return $this;
    }

    public function getUniqueKeys(): array
    {
        return $this->uniqueKeys;
    }
}
