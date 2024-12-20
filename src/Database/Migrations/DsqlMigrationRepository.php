<?php

namespace BreezyBeasts\AuroraDsql\Database\Migrations;

use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Support\Str;

class DsqlMigrationRepository extends DatabaseMigrationRepository
{
    public function createRepository(): void
    {
        $schemaBuilder = $this->getConnection()->getSchemaBuilder();

        $schemaBuilder->create($this->table, function ($table) {
            // Instead of $table->increments('id'), define UUID or ULID:
            $table->ulid('id');
            $table->string('migration');
            $table->integer('batch');
        });
    }

    public function log($file, $batch)
    {
        $record = [
            'id' => (string) Str::ulid(),
            'migration' => $file,
            'batch' => $batch,
        ];
        $this->table()->insert($record);
    }
}
