<?php

declare(strict_types=1);

namespace Infrangible\ETrusted\Model;

use GuzzleHttp\Client;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class AggregateRating
    extends Api
{
    public function getCacheId(): string
    {
        return 'aggregate_rating';
    }

    public function getCacheLifeTime(): int
    {
        return 3600;
    }

    public function getCacheTags(): array
    {
        return ['aggregate_rating'];
    }

    /**
     * @throws \Throwable
     */
    public function getResponseData(Client $client): array
    {
        $accessToken = $this->accessToken->getToken();

        $channelId = $this->storeHelper->getStoreConfig('infrangible_etrusted/channel/channel_id');

        $response = $client->request('GET',
            sprintf('https://api.etrusted.com/channels/%s/service-reviews/aggregate-rating', $channelId), [
                'headers' => [
                    'Accept'        => 'application/json',
                    'Authorization' => sprintf('Bearer %s', $accessToken)
                ],
            ]);

        $responseBody = (string)$response->getBody();

        return $this->json->decode($responseBody);
    }
}