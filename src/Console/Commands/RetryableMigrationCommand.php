<?php

namespace BreezyBeasts\AuroraDsql\Console\Commands;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Console\Migrations\MigrateCommand;
use Illuminate\Database\Migrations\Migrator;

class RetryableMigrationCommand extends MigrateCommand
{
    public function __construct(Migrator $migrator, Dispatcher $dispatcher)
    {
        parent::__construct($migrator, $dispatcher);
        $this->migrator->withinTransaction = false;
    }

    /**
     * Runs migrations with retry logic.
     */
    protected function runMigrations(): mixed
    {
        return retry(5,
            fn () => parent::runMigrations(),
            fn (int $attempt) => $attempt * 1000,
            fn ($e) => $this->isDsqlSerializationError($e));
    }

    /**
     * Check if the provided exception represents a DSQL Serialization error based on specific conditions.
     *
     * @param  Exception  $e  The exception to check
     * @return bool Whether the exception represents a DSQL Serialization error
     */
    private function isDsqlSerializationError($e): bool
    {
        $connection = $this->migrator->resolveConnection($this->option('database'));

        if (
            $e->getCode() === '40001' &&
            str_contains($e->getMessage(), 'OC001') &&
            $connection->getDriverName() === 'aurora_dsql') {
            $this->components->info('Retrying due to DSQL Serialization error OC001...');

            return true;
        }

        return false;
    }
}
