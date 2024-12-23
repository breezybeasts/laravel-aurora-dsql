<?php

namespace BreezyBeasts\AuroraDsql\Database;

use BreezyBeasts\AuroraDsql\Helpers;
use Illuminate\Database\Connectors\PostgresConnector;
use Illuminate\Support\Arr;
use PDO;

class AuroraDsqlPostgresConnector extends PostgresConnector
{
    /**
     * Creates a database connection using the provided DSN, configuration, and options.
     *
     * @param  string  $dsn  The Data Source Name for the database connection
     * @param  array  $config  The configuration array containing region and other connection details
     * @param  array  $options  Additional options for establishing the connection
     * @return PDO A PDO instance representing the database connection
     *
     * @throws InvalidArgumentException if the 'region' key is missing in the configuration array
     * @throws \Exception
     */
    public function createConnection($dsn, array $config, array $options): PDO
    {
        if (! array_key_exists('region', $config)) {
            throw new \InvalidArgumentException('Region must not be empty for Aurora DSQL.');
        }

        $ttl = Arr::get($config, 'expires', '+15 min');
        $token = Helpers::generateDsqlAuthToken($config['host'], $config['region'], $ttl);

        $config['password'] = $token;

        return parent::createConnection($dsn, $config, $options);
    }
}
