<?php

declare(strict_types=1);

namespace Infrangible\ETrusted\Model;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Json;
use FeWeDev\Base\Variables;
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
class Reviews
    extends Api
{
    /** @var Variables */
    protected $variables;

    private $count = 0;

    private $ratings = [];

    private $status = [];

    private $types = [];

    public function __construct(
        ETrusted $cache,
        SerializerInterface $serializer,
        Stores $storeHelper,
        Json $json,
        Arrays $arrays,
        LoggerInterface $logging,
        AccessToken $accessToken,
        Variables $variables)
    {
        parent::__construct($cache, $serializer, $storeHelper, $json, $arrays, $logging, $accessToken);

        $this->variables = $variables;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function setCount(int $count): void
    {
        $this->count = $count;
    }

    public function getRatings(): array
    {
        return $this->ratings;
    }

    public function setRatings(array $ratings): void
    {
        $this->ratings = $ratings;
    }

    public function getStatus(): array
    {
        return $this->status;
    }

    public function setStatus(array $status): void
    {
        $this->status = $status;
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    public function setTypes(array $types): void
    {
        $this->types = $types;
    }

    public function getCacheId(): string
    {
        return 'reviews';
    }

    public function getCacheLifeTime(): int
    {
        return 3600;
    }

    public function getCacheTags(): array
    {
        return ['reviews'];
    }

    public function getResponseData(Client $client): array
    {
        $accessToken = $this->accessToken->getToken();

        $channelId = $this->storeHelper->getStoreConfig('infrangible_etrusted/channel/channel_id');

        $requestUrl =
            sprintf('https://api.etrusted.com/reviews-minimal?channels=%s&count=%d', $channelId, $this->getCount());

        $ratings = $this->getRatings();

        if (count($ratings) > 0) {
            $requestUrl .= sprintf('&rating=%s', implode(',', $ratings));
        }

        $status = $this->getStatus();

        if (count($status) > 0) {
            $requestUrl .= sprintf('&status=%s', implode(',', $status));
        }

        $types = $this->getTypes();

        if (count($types) > 0) {
            $requestUrl .= sprintf('&type=%s', implode(',', $types));
        }

        $response = $client->request('GET', $requestUrl, [
            'headers' => [
                'Accept'        => 'application/json',
                'Authorization' => sprintf('Bearer %s', $accessToken)
            ],
        ]);

        $responseBody = (string)$response->getBody();

        return $this->json->decode($responseBody);
    }
}
