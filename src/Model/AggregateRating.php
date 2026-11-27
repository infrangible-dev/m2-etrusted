<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\ETrusted\Model;

/**
 * The aggregate rating of the service reviews of the channel.
 */
class AggregateRating extends Api
{
    /**
     * Returns the aggregate rating by period ("7days", "30days", "90days", "365days", "overall").
     *
     * Every period has the count of reviews and the average rating. The data is empty if the channel is missing or
     * the request failed.
     *
     * @return array
     * @phpstan-return array<int|string, mixed>
     */
    public function getAggregateRating(): array
    {
        $channelId = $this->getChannelId();

        if ($channelId === '') {
            return [];
        }

        return $this->getApiData(sprintf('/channels/%s/service-reviews/aggregate-rating', rawurlencode($channelId)));
    }
}
