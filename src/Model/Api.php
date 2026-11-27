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
use Magento\Framework\HTTP\Client\CurlFactory;
use Psr\Log\LoggerInterface;
use RuntimeException;

/**
 * Requests data of the channel that the store configures from the eTrusted API.
 */
abstract class Api extends Base
{
    /** URL of the API. */
    public const URL = 'https://api.etrusted.com';

    /** Config path of the channel ID. */
    public const CONFIG_CHANNEL_ID = 'infrangible_etrusted/channel/channel_id';

    /**
     * @param ETrusted $cache
     * @param Stores $storeHelper
     * @param Json $json
     * @param Variables $variables
     * @param LoggerInterface $logging
     * @param CurlFactory $curlFactory
     * @param AccessToken $accessToken
     */
    public function __construct(
        ETrusted $cache,
        Stores $storeHelper,
        Json $json,
        Variables $variables,
        LoggerInterface $logging,
        CurlFactory $curlFactory,
        protected AccessToken $accessToken
    ) {
        parent::__construct($cache, $storeHelper, $json, $variables, $logging, $curlFactory);
    }

    /**
     * Returns the channel ID of the store; empty if it is missing.
     *
     * @return string
     */
    protected function getChannelId(): string
    {
        return trim(
            $this->variables->stringValueOrDefault($this->storeHelper->getStoreConfig(self::CONFIG_CHANNEL_ID))
        );
    }

    /**
     * Returns the response of the API to the path, from the cache or requested with the access token.
     *
     * The response is empty if the request failed (the error is logged).
     *
     * @param string $path for example "/channels/chl-1/service-reviews/aggregate-rating"
     *
     * @return array
     * @phpstan-return array<int|string, mixed>
     */
    protected function getApiData(string $path): array
    {
        return $this->getCachedData(
            self::URL . $path,
            function () use ($path): array {
                $accessToken = $this->accessToken->getToken();

                if ($accessToken === '') {
                    throw new RuntimeException(
                        'eTrusted gave no access token, check the client ID and the secret in the configuration'
                    );
                }

                return $this->request(
                    self::URL . $path,
                    ['Accept' => 'application/json', 'Authorization' => sprintf('Bearer %s', $accessToken)]
                );
            }
        );
    }
}
