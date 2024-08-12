<?php

declare(strict_types=1);

namespace Infrangible\ETrusted\Block\Widget\Reviews\Item;

use Magento\Framework\View\Element\Template;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Rating extends Template
{
    /** @var float */
    private $rating;

    public function getRating(): float
    {
        return $this->rating;
    }

    public function setRating(float $rating): void
    {
        $this->rating = $rating;
    }
}
