<?php

namespace OnSecurity\Kafkavel\Auth;

use Aws\Credentials\CredentialProvider;
use Aws\Signature\SignatureV4;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

/**
 * Generates OAUTHBEARER tokens for AWS MSK IAM authentication.
 *
 * Produces a SigV4 presigned URL that librdkafka sends as the OAUTHBEARER
 * token value. The MSK broker validates the signature to authenticate the
 * caller's IAM identity (Pod Identity on EKS injects the credentials).
 */
class MskIamTokenProvider
{
    private string $region;
    private int $lifetimeSeconds;

    public function __construct(string $region = 'eu-west-2', int $lifetimeSeconds = 900)
    {
        $this->region = $region;
        $this->lifetimeSeconds = $lifetimeSeconds;
    }

    /**
     * Generate an auth token suitable for setOAuthBearerToken().
     *
     * @return array{token: string, lifetime_ms: int}
     */
    public function generateToken(): array
    {
        $credentials = CredentialProvider::defaultProvider()()->wait();
        $signer = new SignatureV4('kafka-cluster', $this->region);

        $uri = (new Uri())
            ->withScheme('https')
            ->withHost("kafka.{$this->region}.amazonaws.com")
            ->withPath('/')
            ->withQuery(http_build_query([
                'Action' => 'kafka-cluster:Connect',
            ]));

        $request = new Request('GET', $uri, [
            'host' => "kafka.{$this->region}.amazonaws.com",
        ]);

        $presigned = $signer->presign($request, $credentials, '+' . $this->lifetimeSeconds . ' seconds');
        $token = $presigned->getUri()->__toString();

        $lifetimeMs = (time() + $this->lifetimeSeconds) * 1000;

        return [
            'token' => $token,
            'lifetime_ms' => $lifetimeMs,
        ];
    }

    /**
     * Get a callback suitable for RdKafka\Conf::setOAuthBearerTokenRefreshCb().
     *
     * @return callable
     */
    public function getRefreshCallback(): callable
    {
        return function ($kafka, $oauthbearerConfig) {
            try {
                $result = $this->generateToken();
                $kafka->setOAuthBearerToken(
                    $result['token'],
                    $result['lifetime_ms'],
                    'kafka-client',
                );
            } catch (\Throwable $e) {
                $kafka->setOAuthBearerTokenFailure($e->getMessage());
            }
        };
    }
}
