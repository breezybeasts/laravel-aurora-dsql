<?php

namespace BreezyBeasts\AuroraDsql\Schema;

use Illuminate\Database\Schema\ForeignIdColumnDefinition;

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
        return $this->uuid($column);
    }

    /**
     * Create a new UUID column on the table.
     *
     * @param  string  $column
     * @return \Illuminate\Database\Schema\ForeignIdColumnDefinition
     */
    public function foreignId($column)
    {
        return $this->addColumnDefinition(new ForeignIdColumnDefinition($this, [
            'type' => 'uuid',
            'name' => $column,
        ]));
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
