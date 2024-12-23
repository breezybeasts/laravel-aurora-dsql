<?php

namespace BreezyBeasts\AuroraDsql;

use Aws\Credentials\CredentialProvider;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

class Helpers
{
    /**
     * Generates a pre-signed URL for Aurora DSQL authentication.
     *
     * @param  string  $hostname  The hostname for the Aurora DSQL service.
     * @param  string  $region  The AWS region where the service is located.
     * @param  int  $ttl  The time-to-live for the pre-signed URL.
     * @return string The pre-signed URL for Aurora DSQL authentication.
     */
    public static function generateDsqlAuthToken($hostname, $region, $ttl): string
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
        return substr($presignedUrl, strlen('https://'));

    }
}
