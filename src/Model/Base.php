<?php

declare(strict_types=1);

namespace Infrangible\ETrusted\Model;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Json;
use GuzzleHttp\Client;
use Infrangible\Core\Helper\Stores;
use Infrangible\ETrusted\Model\Cache\Type\ETrusted;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
abstract class Base
{
    /** @var ETrusted */
    protected $cache;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var Stores */
    protected $storeHelper;

    /** @var Json */
    protected $json;

    /** @var Arrays */
    protected $arrays;

    /** @var LoggerInterface */
    protected $logging;

    public function __construct(
        ETrusted $cache,
        SerializerInterface $serializer,
        Stores $storeHelper,
        Json $json,
        Arrays $arrays,
        LoggerInterface $logging
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->storeHelper = $storeHelper;
        $this->json = $json;
        $this->arrays = $arrays;
        $this->logging = $logging;
    }

    abstract public function getCacheId(): string;

    abstract public function getCacheLifeTime(): int;

    abstract public function getCacheTags(): array;

    public function get(): array
    {
        $cacheId = sprintf(
            '%s_%s',
            ETrusted::TYPE_IDENTIFIER,
            $this->getCacheId()
        );

        $responseData = $this->cache->load($cacheId);

        if ($responseData) {
            $responseData = $this->serializer->unserialize($responseData);
        } else {
            $client = new Client();

            try {
                $responseData = $this->getResponseData($client);

                $this->cache->save(
                    $this->serializer->serialize($responseData),
                    $cacheId,
                    $this->getCacheTags(),
                    $this->getCacheLifeTime()
                );
            } catch (\Throwable $exception) {
                $this->logging->error($exception);

                $responseData = [];
            }
        }

        return $responseData;
    }

    /**
     * @throws \Throwable
     */
    abstract public function getResponseData(Client $client): array;
}
