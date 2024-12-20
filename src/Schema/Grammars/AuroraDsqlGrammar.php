<?php

namespace BreezyBeasts\AuroraDsql\Schema\Grammars;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Illuminate\Support\Fluent;
use RuntimeException;

class AuroraDsqlGrammar extends PostgresGrammar
{
    /**
     * While DSQL does support transaction running migrations in transactions causes many issue
     * To work as seamless as possible default to false here
     */
    public function supportsSchemaTransactions(): bool
    {
        return false;
    }

    public function supportsSavepoints()
    {
        return false;
    }

    protected function modifyNullable(Blueprint $blueprint, Fluent $column)
    {
        // modifiers not supported
    }

    public function compileDropColumn(\Illuminate\Database\Schema\Blueprint $blueprint, \Illuminate\Support\Fluent $command)
    {
        // Throw an exception or log a message to prevent unsupported operations
        throw new \RuntimeException('Dropping columns is not supported in Aurora DSQL. Consider recreating and migrating the table or hide columns.');
    }

    public function compileAsyncIndex(Blueprint $blueprint, Fluent $command): string
    {
        $columns = $this->columnize($command->columns);
        $includedColumns = $this->columnize($command->includedColumns);
        $ifNotExists = $command->ifNotExists ? 'IF NOT EXISTS ' : '';
        $unique = $command->unique ? 'UNIQUE ' : '';
        $includedColumns = ! empty($command->includedColumns) ? "INCLUDE ({$includedColumns}) " : '';
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

        if (! empty($primaryKeys)) {
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

    /**
     * Create the column definition for an integer type.
     *
     * @return string
     */
    protected function typeInteger(Fluent $column)
    {
        return 'integer';
    }

    /**
     * Create the column definition for a big integer type.
     *
     * @return string
     */
    protected function typeBigInteger(Fluent $column)
    {
        return 'bigint';
    }

    /**
     * Create the column definition for a small integer type.
     *
     * @return string
     */
    protected function typeSmallInteger(Fluent $column)
    {
        return 'smallint';
    }

    /**
     * Create the column definition for an enumeration type.
     *
     * @return string
     */
    protected function typeEnum(Fluent $column)
    {
        throw new RuntimeException('This database driver does not support the enum type.');
    }

    /**
     * Create the column definition for a vector type.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected function typeVector(Fluent $column)
    {
        throw new RuntimeException('This database driver does not support the vector type.');
    }

    /**
     * Create the column definition for a spatial Geography type.
     *
     * @return string
     */
    protected function typeGeography(Fluent $column)
    {
        throw new RuntimeException('This database driver does not support the geography type.');
    }

    /**
     * Create the column definition for a MAC address type.
     *
     * @return string
     */
    protected function typeMacAddress(Fluent $column)
    {
        throw new RuntimeException('This database driver does not support the macaddr type.');
    }

    /**
     * Create the column definition for an IP address type.
     *
     * @return string
     */
    protected function typeIpAddress(Fluent $column)
    {
        throw new RuntimeException('This database driver does not support the inet type.');
    }
}
