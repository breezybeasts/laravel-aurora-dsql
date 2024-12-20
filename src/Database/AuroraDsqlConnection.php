<?php

namespace BreezyBeasts\AuroraDsql\Database;

use BreezyBeasts\AuroraDsql\Schema\Grammars\AuroraDsqlGrammar;
use BreezyBeasts\AuroraDsql\Schema\Grammars\AuroraDsqlQueryGrammar;
use BreezyBeasts\AuroraDsql\Schema\SchemaBuilder;
use Illuminate\Database\PostgresConnection;

class AuroraDsqlConnection extends PostgresConnection
{
    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        parent::__construct($pdo, $database, $tablePrefix, $config);
        // Explicitly set the schema grammar
        $this->useDefaultSchemaGrammar();
        // Aurora DSQL doesn't preserve statement names that you prepare.
        $this->getPdo()->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
    }

    public function bindValues($statement, $bindings)
    {
        parent::bindValues($statement, $bindings); // TODO: Change the autogenerated stub
    }

    protected function getDefaultQueryGrammar()
    {
        ($grammar = new AuroraDsqlQueryGrammar)->setConnection($this);

        return $this->withTablePrefix($grammar);
    }

    public function getDefaultSchemaGrammar()
    {
        // Return your custom grammar
        return $this->withTablePrefix(new AuroraDsqlGrammar);
    }

    public function getSchemaBuilder(): SchemaBuilder
    {
        return new SchemaBuilder($this);
    }
}
