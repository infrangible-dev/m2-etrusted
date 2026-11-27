<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\ETrusted\Model;

use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Stores;
use Infrangible\ETrusted\Model\Cache\Type\ETrusted;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\HTTP\Client\CurlFactory;
use Psr\Log\LoggerInterface;

/**
 * The access token of the API client that the store configures (Stores > Configuration > Infrangible > eTrusted).
 */
class AccessToken extends Base
{
    /** URL that issues access tokens. */
    public const URL = 'https://login.etrusted.com/oauth/token';

    /** Config path of the client ID. */
    public const CONFIG_CLIENT_ID = 'infrangible_etrusted/sso/client_id';

    /** Config path of the secret (encrypted). */
    public const CONFIG_SECRET = 'infrangible_etrusted/sso/secret';

    /**
     * @param ETrusted $cache
     * @param Stores $storeHelper
     * @param Json $json
     * @param Variables $variables
     * @param LoggerInterface $logging
     * @param CurlFactory $curlFactory
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ETrusted $cache,
        Stores $storeHelper,
        Json $json,
        Variables $variables,
        LoggerInterface $logging,
        CurlFactory $curlFactory,
        protected EncryptorInterface $encryptor
    ) {
        parent::__construct($cache, $storeHelper, $json, $variables, $logging, $curlFactory);
    }

    /**
     * Returns the access token; empty if the client ID or the secret are missing or eTrusted declined them.
     *
     * @return string
     */
    public function getToken(): string
    {
        $clientId = $this->variables->stringValueOrDefault($this->storeHelper->getStoreConfig(self::CONFIG_CLIENT_ID));
        $secret = $this->encryptor->decrypt(
            $this->variables->stringValueOrDefault($this->storeHelper->getStoreConfig(self::CONFIG_SECRET))
        );

        if ($clientId === '' || $secret === '') {
            return '';
        }

        $data = $this->getCachedData(
            sprintf("access_token\n%s\n%s", $clientId, $secret),
            fn(): array => $this->request(
                self::URL,
                ['Accept' => 'application/json', 'Content-Type' => 'application/x-www-form-urlencoded'],
                [
                    'client_id'     => $clientId,
                    'client_secret' => $secret,
                    'grant_type'    => 'client_credentials',
                    'audience'      => Api::URL
                ]
            )
        );

        return $this->variables->stringValueOrDefault($data[ 'access_token' ] ?? null);
    }

    /**
     * Returns the seconds to keep the token in the cache.
     *
     * A minute less than the token is valid, so that it does not expire between loading and using it.
     *
     * @param array $data
     * @phpstan-param array<int|string, mixed> $data
     *
     * @return int
     */
    protected function getCacheLifetime(array $data): int
    {
        $expiresIn = $data[ 'expires_in' ] ?? null;

        return is_numeric($expiresIn) ? max(60, $this->variables->intValue($expiresIn) - 60) : self::CACHE_LIFETIME;
    }
}
