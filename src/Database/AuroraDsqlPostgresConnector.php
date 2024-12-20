<?php

namespace BreezyBeasts\AuroraDsql\Database;

use Aws\Credentials\CredentialProvider;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Database\Connectors\PostgresConnector;
use Illuminate\Support\Arr;
use PDO;

class AuroraDsqlPostgresConnector extends PostgresConnector
{
    public function createConnection($dsn, array $config, array $options): PDO
    {
        if (! array_key_exists('region', $config)) {
            throw new \InvalidArgumentException('Region must not be empty for Aurora DSQL.');
        }

        $ttl = Arr::get($config, 'expires', '+15 min');
        $token = $this->generateDsqlAuthToken($config['host'], $config['region'], $ttl);

        $config['password'] = $token;

        return parent::createConnection($dsn, $config, $options);
    }

    protected function generateDsqlAuthToken($hostname, $region, $ttl): string
    {

        $hostname = trim($hostname);
        $region = trim($region);
        if (empty($hostname)) {
            throw new \InvalidArgumentException('Hostname must not be empty for Aurora DSQL.');
        }

        if (empty($region)) {
            throw new \InvalidArgumentException('Region must not be empty for Aurora DSQL.');
        }

        $provider = CredentialProvider::defaultProvider();

        $credentials = $provider()->wait();

        $base_uri = (new Uri)->withScheme('https')
            ->withHost($hostname)
            ->withQuery(http_build_query(['Action' => 'DbConnectAdmin']));

        $request = new Request('GET', $base_uri);
        $signer = new SignatureV4('dsql', $region);
        $presignedRequest = $signer->presign($request, $credentials, $ttl);
        $presignedUrl = (string) $presignedRequest->getUri();

        // Return everything after "https://"
        $token = substr($presignedUrl, strlen('https://'));

        return $token;

    }
}
