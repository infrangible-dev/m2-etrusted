<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\ETrusted\Block\Widget\Reviews\Item;

use Magento\Framework\View\Element\Template;

/**
 * The rating of a review as stars.
 */
class Rating extends Template
{
    /** Highest possible rating. */
    public const MAX_RATING = 5;

    /** @var float the rating, 1 to 5 */
    private float $rating = 0.0;

    /**
     * Returns the rating, 1 to 5.
     *
     * @return float
     */
    public function getRating(): float
    {
        return $this->rating;
    }

    /**
     * Sets the rating, 1 to 5.
     *
     * @param float $rating
     *
     * @return void
     */
    public function setRating(float $rating): void
    {
        $this->rating = $rating;
    }

    /**
     * Returns the rating in percent of the highest rating, for the width of the stars.
     *
     * @return int
     */
    public function getPercent(): int
    {
        return (int)ceil(min(max($this->rating, 0), self::MAX_RATING) / self::MAX_RATING * 100);
    }
}
