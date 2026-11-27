<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\ETrusted\Block\Widget\Reviews;

use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Stores;
use Infrangible\ETrusted\Helper\Block;
use Magento\Framework\View\Element\Template;

/**
 * A review in the slider of the reviews widget.
 */
class Item extends Template
{
    /** Config path of the trust mark image, relative to the directory infrangible/etrusted of the media. */
    public const CONFIG_TRUST_MARK = 'infrangible_etrusted/display/trust_mark';

    /** @var array<string, mixed> rating, name, comment and date */
    private array $review = [];

    /**
     * @param Template\Context $context
     * @param Block $blockHelper
     * @param Stores $storeHelper
     * @param Variables $variables
     * @param array $data
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        Template\Context $context,
        protected Block $blockHelper,
        protected Stores $storeHelper,
        protected Variables $variables,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Returns the review with rating, name, comment and date.
     *
     * @return array<string, mixed>
     */
    public function getReview(): array
    {
        return $this->review;
    }

    /**
     * Sets the review with rating, name, comment and date.
     *
     * @param array $review
     * @phpstan-param array<string, mixed> $review
     *
     * @return void
     */
    public function setReview(array $review): void
    {
        $this->review = $review;
    }

    /**
     * Returns the HTML of the rating of a review.
     *
     * @param array $review
     * @param string $templateName
     * @phpstan-param array<string, mixed> $review
     *
     * @return string
     */
    public function getRatingHtml(
        array $review,
        string $templateName = 'Infrangible_ETrusted::widget/reviews/item/rating.phtml'
    ): string {
        return $this->blockHelper->getRatingHtml($this, $review, $templateName);
    }

    /**
     * Returns the URL of the trust mark image of the configuration; empty if there is none.
     *
     * @return string
     */
    public function getTrustMarkUrl(): string
    {
        $trustMark = $this->variables->stringValueOrDefault(
            $this->storeHelper->getStoreConfig(self::CONFIG_TRUST_MARK)
        );

        if ($trustMark === '') {
            return '';
        }

        return sprintf('%sinfrangible/etrusted/%s', $this->storeHelper->getMediaUrl(), $trustMark);
    }
}
