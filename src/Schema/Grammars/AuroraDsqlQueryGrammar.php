<?php

namespace BreezyBeasts\AuroraDsql\Schema\Grammars;

use Illuminate\Database\Query\Grammars\PostgresGrammar;

class AuroraDsqlQueryGrammar extends PostgresGrammar
{
    /**
     * Check if the current database system supports savepoints.
     * Aurora DSQL does NOT support savepoints
     */
    public function supportsSavepoints(): bool
    {
        return false;
    }
}
