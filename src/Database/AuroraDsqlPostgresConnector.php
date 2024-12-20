<?php

namespace BreezyBeasts\AuroraDsql\Database;

use Aws\Credentials\CredentialProvider;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Illuminate\Database\Connectors\PostgresConnector;
use PDO;

class AuroraDsqlPostgresConnector extends PostgresConnector
{
    public function createConnection($dsn, array $config, array $options): PDO
    {
        $token = $this->generateDsqlAuthToken($config['host'], $config['region'], $config['token_ttl']);

        $config['password'] = $token;

        return parent::createConnection($dsn, $config, $options); // TODO: Change the autogenerated stub
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
        $presignedRequest = $signer->presign($request, $credentials, '+1 hour');
        $presignedUrl = (string) $presignedRequest->getUri();

        // Return everything after "https://"
        $token = substr($presignedUrl, strlen('https://'));

        return $token;

    }
}
