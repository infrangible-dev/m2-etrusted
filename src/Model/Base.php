<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\ETrusted\Model;

use Exception;
use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Stores;
use Infrangible\ETrusted\Model\Cache\Type\ETrusted;
use Magento\Framework\HTTP\Client\CurlFactory;
use Psr\Log\LoggerInterface;
use RuntimeException;
use UnexpectedValueException;

/**
 * Requests data from eTrusted and caches it in the cache type "etrusted".
 */
abstract class Base
{
    /** Seconds to wait for eTrusted. */
    public const TIMEOUT = 10;

    /** Seconds to keep the data in the cache. */
    public const CACHE_LIFETIME = 3600;

    /**
     * @param ETrusted $cache
     * @param Stores $storeHelper
     * @param Json $json
     * @param Variables $variables
     * @param LoggerInterface $logging
     * @param CurlFactory $curlFactory
     */
    public function __construct(
        protected ETrusted $cache,
        protected Stores $storeHelper,
        protected Json $json,
        protected Variables $variables,
        protected LoggerInterface $logging,
        protected CurlFactory $curlFactory
    ) {
    }

    /**
     * Returns the data from the cache, or requests and caches it.
     *
     * The key identifies the request (URL, credentials), so stores with other settings and changed settings get their
     * own data. The data is empty if the request failed (the error is logged); failures are not cached.
     *
     * @param string $key
     * @param callable $request returns the data, throws an exception if the request failed
     * @phpstan-param callable(): array<int|string, mixed> $request
     *
     * @return array
     * @phpstan-return array<int|string, mixed>
     */
    protected function getCachedData(string $key, callable $request): array
    {
        $cacheId = sprintf('%s_%s', ETrusted::TYPE_IDENTIFIER, hash('sha256', $key));

        $cachedData = $this->cache->load($cacheId);

        if (is_string($cachedData) && $cachedData !== '') {
            $data = $this->json->decode($cachedData);

            if (is_array($data)) {
                return $data;
            }
        }

        try {
            $data = $request();
        } catch (Exception $exception) {
            $this->logging->error($exception);

            return [];
        }

        $encodedData = $this->json->encode($data);

        if ($encodedData !== null) {
            $this->cache->save($encodedData, $cacheId, [ETrusted::CACHE_TAG], $this->getCacheLifetime($data));
        }

        return $data;
    }

    /**
     * Returns the seconds to keep the data in the cache.
     *
     * @param array $data
     * @phpstan-param array<int|string, mixed> $data
     *
     * @return int
     */
    protected function getCacheLifetime(array $data): int
    {
        return self::CACHE_LIFETIME;
    }

    /**
     * Sends a request to eTrusted and returns the decoded JSON response.
     *
     * The request is a POST request with the form parameters, a GET request without.
     *
     * @param string $url
     * @param array $headers
     * @param array|null $formParameters
     * @phpstan-param array<string, string> $headers
     * @phpstan-param array<string, string>|null $formParameters
     *
     * @return array
     * @phpstan-return array<int|string, mixed>
     * @throws Exception
     */
    protected function request(string $url, array $headers, ?array $formParameters = null): array
    {
        $client = $this->curlFactory->create();
        $client->setTimeout(self::TIMEOUT);
        $client->setHeaders($headers);

        if ($formParameters === null) {
            $client->get($url);
        } else {
            $client->post($url, $formParameters);
        }

        $status = $client->getStatus();
        $body = $client->getBody();

        if ($status !== 200) {
            throw new RuntimeException(
                sprintf('eTrusted answered the request %s with status %d: %s', $url, $status, $body)
            );
        }

        $data = $this->json->decode($body);

        if (! is_array($data)) {
            throw new UnexpectedValueException(sprintf('eTrusted answered the request %s with: %s', $url, $body));
        }

        return $data;
    }
}
