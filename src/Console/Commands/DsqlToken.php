<?php

namespace BreezyBeasts\AuroraDsql\Console\Commands;

use BreezyBeasts\AuroraDsql\Helpers;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class DsqlToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dsql:token {connection=default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a pre-signed URL for Aurora DSQL authentication';

    public function handle(): void
    {
        if ($this->argument('connection') == 'default') {
            $connection = config('database.default');
            $config = config('database.connections.'.$connection);
        } else {
            $config = config('database.connections.'.$this->argument('connection'));
        }

        $ttl = Arr::get($config, 'expires', '+15 min');
        $token = Helpers::generateDsqlAuthToken($config['username'], $config['host'], $config['region'], $ttl);

        $this->info($token);
    }
}
