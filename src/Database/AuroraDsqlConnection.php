<?php

namespace BreezyBeasts\AuroraDsql\Database;

use BreezyBeasts\AuroraDsql\Schema\Grammars\AuroraDsqlGrammar;
use BreezyBeasts\AuroraDsql\Schema\Grammars\AuroraDsqlQueryGrammar;
use BreezyBeasts\AuroraDsql\Schema\SchemaBuilder;
use Illuminate\Database\PostgresConnection;
use PDO;

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

    public function bindValues($statement, $bindings): void
    {

        foreach ($bindings as $key => $value) {
            $parameter = is_string($key) ? $key : $key + 1;

            switch (true) {
                case is_bool($value):
                    $dataType = PDO::PARAM_BOOL;
                    break;

                case $value === null:
                    $dataType = PDO::PARAM_NULL;
                    break;

                default:
                    $dataType = PDO::PARAM_STR;
            }

            $statement->bindValue($parameter, $value, $dataType);
        }

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
