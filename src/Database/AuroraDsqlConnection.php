<?php

namespace BreezyBeasts\AuroraDsql\Database;

use BreezyBeasts\AuroraDsql\Schema\Grammars\AuroraDsqlGrammar;
use BreezyBeasts\AuroraDsql\Schema\SchemaBuilder;
use Illuminate\Database\PostgresConnection;

class AuroraDsqlConnection extends PostgresConnection
{

    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        parent::__construct($pdo, $database, $tablePrefix, $config);

        // Explicitly set the schema grammar
        $this->useDefaultSchemaGrammar();
    }

    public function getDefaultSchemaGrammar()
    {
        // Return your custom grammar
        return $this->withTablePrefix(new AuroraDsqlGrammar());
    }

    public function getSchemaBuilder(): SchemaBuilder
    {
        return new SchemaBuilder($this);
    }
}
