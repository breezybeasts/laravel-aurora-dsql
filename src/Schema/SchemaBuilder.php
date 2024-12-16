<?php

namespace BreezyBeasts\AuroraDsql\Schema;

use Illuminate\Database\Schema\PostgresBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Fluent;

class SchemaBuilder extends PostgresBuilder
{
    protected function createBlueprint($table, \Closure $callback = null): Blueprint
    {
        return new Blueprint($table, $callback);
    }

    public function dropAllTables(): void
    {
        // Get the schema name from the connection configuration or default to 'public'
        $schema = $this->connection->getConfig('search_path') ?: 'public';

        $tables = DB::select("
            SELECT tablename 
            FROM pg_tables 
            WHERE schemaname = ?
        ", [$schema]);

        // Drop each table dynamically
        foreach ($tables as $table) {
            $this->connection->statement("DROP TABLE IF EXISTS \"{$table->tablename}\" CASCADE");
        }
    }
}
