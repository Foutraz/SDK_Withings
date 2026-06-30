<?php

namespace Foutraz\Withings\Actions;

use Foutraz\Withings\Dto\TokenResponse;
use Foutraz\Withings\Exceptions\ActionFailed;
use Foutraz\Withings\Exceptions\InvalidData;
use Foutraz\Withings\Exceptions\ResourceNotFound;
use Foutraz\Withings\Exceptions\TooManyRequestsException;
use Foutraz\Withings\Exceptions\Unauthorized;
use Foutraz\Withings\WithingsManager;
use GuzzleHttp\Exception\GuzzleException;

class ManagesAuthentication extends WithingsManager
{
    /** @param array<int, string> $scopes */
    public function authorizeUrl(array $scopes = ['user.metrics'], string $state = ''): string
    {
        return 'https://account.withings.com/oauth2_user/authorize2?'.http_build_query([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'scope' => implode(',', $scopes),
            'redirect_uri' => $this->redirectUri,
            'state' => $state,
        ]);
    }

    /**
     * @throws ActionFailed
     * @throws GuzzleException
     * @throws InvalidData
     * @throws ResourceNotFound
     * @throws TooManyRequestsException
     * @throws Unauthorized
     */
    public function exchangeToken(string $code): TokenResponse
    {
        return TokenResponse::fromArray($this->post('https://wbsapi.withings.net/v2/oauth2', [
            'action' => 'requesttoken',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
        ]));
    }

    /**
     * @throws ActionFailed
     * @throws GuzzleException
     * @throws InvalidData
     * @throws ResourceNotFound
     * @throws TooManyRequestsException
     * @throws Unauthorized
     */
    public function refreshToken(string $refreshToken): TokenResponse
    {
        return TokenResponse::fromArray($this->post('https://wbsapi.withings.net/v2/oauth2', [
            'action' => 'requesttoken',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]));
    }
}
