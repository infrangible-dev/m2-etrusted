<?php

declare(strict_types=1);

namespace Infrangible\ETrusted\Model;

use GuzzleHttp\Client;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class AccessToken extends Base
{
    private $cacheLifeTime = 3600;

    /**
     * @throws \Exception
     */
    public function getToken(): string
    {
        $responseData = $this->get();

        return $this->arrays->getValue($responseData, 'access_token');
    }

    public function getCacheId(): string
    {
        return 'access_token';
    }

    public function getCacheLifeTime(): int
    {
        return $this->cacheLifeTime;
    }

    public function setCacheLifeTime(int $cacheLifeTime): void
    {
        $this->cacheLifeTime = $cacheLifeTime;
    }

    public function getCacheTags(): array
    {
        return ['access_token'];
    }

    public function getResponseData(Client $client): array
    {
        $clientId = $this->storeHelper->getStoreConfig('infrangible_etrusted/sso/client_id');
        $secret = $this->storeHelper->getStoreConfig('infrangible_etrusted/sso/secret');

        $formParameters = [
            'client_id'     => $clientId,
            'client_secret' => $secret,
            'grant_type'    => 'client_credentials',
            'audience'      => 'https://api.etrusted.com'
        ];

        $response = $client->request('POST', 'https://login.etrusted.com/oauth/token', [
            'form_params' => $formParameters,
            'headers'     => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        $responseBody = (string)$response->getBody();

        $responseData = $this->json->decode($responseBody);

        $expiresIn = $this->arrays->getValue($responseData, 'expires_in', 3600);

        $this->setCacheLifeTime($expiresIn);

        return $responseData;
    }
}
