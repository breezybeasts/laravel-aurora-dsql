<?php

namespace BreezyBeasts\AuroraDsql\Providers;

use BreezyBeasts\AuroraDsql\Console\Commands\DsqlToken;
use BreezyBeasts\AuroraDsql\Console\Commands\RetryableMigrationCommand;
use BreezyBeasts\AuroraDsql\Database\AuroraDsqlConnection;
use BreezyBeasts\AuroraDsql\Database\AuroraDsqlPostgresConnector;
use BreezyBeasts\AuroraDsql\Database\Migrations\DsqlMigrationRepository;
use Illuminate\Support\ServiceProvider;

class AuroraDsqlServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->resolving('db', function ($db) {
            $db->extend('aurora_dsql', function ($config, $name) {
                $config['name'] = $name;
                $connector = new AuroraDsqlPostgresConnector;
                $pdo = $connector->connect($config);

                return new AuroraDsqlConnection($pdo, $config['database'], $config['prefix'] ?? '', $config);
            });
        });

        $this->app->extend('migration.repository', function ($defaultRepo, $app) {
            // custom table structure to remove serial id from migrations table
            return new DsqlMigrationRepository($app['db'], $app['config']['database.migrations.table']);
        });

        $this->mergeConfigFrom(__DIR__.'/../config/auora_dsql.php', 'auora_dsql');

    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/auora_dsql.php' => config_path('auora_dsql.php'),
        ], 'laravel-aurora-dsql-config');

        if ($this->app->runningInConsole()) {

            // Replace the default migrate command

            $this->app->singleton('command.migrate', function ($app) {
                return new RetryableMigrationCommand(
                    $app['migrator'],
                    $app['events']
                );
            });

            $this->commands([
                'command.migrate',
                DsqlToken::class,
            ]);
        }

    }
}
