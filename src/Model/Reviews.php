<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\ETrusted\Model;

/**
 * The latest reviews of the channel.
 */
class Reviews extends Api
{
    /**
     * Returns the latest reviews, each with rating, comment and editedAt, among others.
     *
     * The list is empty if the channel is missing or the request failed.
     *
     * @param int $count
     * @param string[] $ratings only reviews with these ratings ("1" to "5"); all if empty
     * @param string[] $status only reviews with this status ("APPROVED", "MODERATION", "REJECTED"); all if empty
     * @param string[] $types only reviews of these types ("SERVICE_REVIEW", "PRODUCT_REVIEW"); all if empty
     *
     * @return array
     * @phpstan-return array<int|string, mixed>
     */
    public function getReviews(int $count, array $ratings = [], array $status = [], array $types = []): array
    {
        $channelId = $this->getChannelId();

        if ($channelId === '') {
            return [];
        }

        $query = sprintf('channels=%s&count=%d', rawurlencode($channelId), $count);

        foreach (['rating' => $ratings, 'status' => $status, 'type' => $types] as $name => $values) {
            if (count($values) > 0) {
                $query .= sprintf('&%s=%s', $name, implode(',', array_map('rawurlencode', $values)));
            }
        }

        $items = $this->getApiData(sprintf('/reviews-minimal?%s', $query))[ 'items' ] ?? [];

        return is_array($items) ? $items : [];
    }
}
