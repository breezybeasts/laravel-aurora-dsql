<?php

namespace BreezyBeasts\AuroraDsql\Schema\Grammars;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Illuminate\Support\Fluent;

class AuroraDsqlGrammar extends PostgresGrammar
{

    protected function modifyNullable(Blueprint $blueprint, Fluent $column)
    {

    }

    public function compileDropColumn(\Illuminate\Database\Schema\Blueprint $blueprint, \Illuminate\Support\Fluent $command)
    {
        // Throw an exception or log a message to prevent unsupported operations
        throw new \RuntimeException("Dropping columns is not supported in Aurora DSQL. Consider recreating the table.");
    }



    public function compileAsyncIndex(Blueprint $blueprint, Fluent $command): string
    {
        $columns = $this->columnize($command->columns);
        $includedColumns = $this->columnize($command->includedColumns);
        $ifNotExists = $command->ifNotExists ? 'IF NOT EXISTS ' : '';
        $unique = $command->unique ? 'UNIQUE ' : '';
        $includedColumns = !empty($command->includedColumns) ? "INCLUDE ({$includedColumns}) " : '';
        $nullsPosition = $command->nullsPosition ? " NULLS {$command->nullsPosition}" : '';

        return sprintf(
            'CREATE %sINDEX ASYNC %s%s ON %s (%s) %s%s',
            $unique,
            $ifNotExists,
            $this->wrap($command->indexName),
            $this->wrapTable($blueprint),
            $columns,
            $includedColumns,
            $nullsPosition
        );
    }


    protected function compilePrimaryKey(\Illuminate\Database\Schema\Blueprint $blueprint): string
    {
        $primaryKeys = $blueprint->getPrimaryKeys();

        if (!empty($primaryKeys)) {
            $columns = implode(', ', array_map([$this, 'wrap'], $primaryKeys));
            return sprintf(', PRIMARY KEY (%s)', $columns);
        }

        return '';
    }

    protected function compileUniqueKeys(\Illuminate\Database\Schema\Blueprint $blueprint)
    {
        $uniqueKeys = $blueprint->getUniqueKeys();

        $compiledKeys = array_map(function ($uniqueKey) {
            $columns = implode(', ', array_map([$this, 'wrap'], $uniqueKey['columns']));
            return sprintf(', UNIQUE (%s)', $columns);
        }, $uniqueKeys);

        return implode('', $compiledKeys);
    }

    public function compileCreate(Blueprint $blueprint, Fluent $command): string
    {
        $columns = implode(', ', $this->getColumns($blueprint));

        $primaryKey = $this->compilePrimaryKey($blueprint);
        $uniqueKeys = $this->compileUniqueKeys($blueprint);

        return sprintf(
            'CREATE TABLE %s (%s%s%s)',
            $this->wrapTable($blueprint),
            $columns,
            $primaryKey,
            $uniqueKeys
        );
    }


}
