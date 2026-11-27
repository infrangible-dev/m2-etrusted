<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\ETrusted\Helper;

use Infrangible\ETrusted\Block\Widget\Reviews\ItemFactory;
use Infrangible\ETrusted\Block\Widget\Reviews\Item\RatingFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Renders the reviews and their ratings of the reviews widget; the Hyvä module uses it with its own templates.
 */
class Block extends AbstractHelper
{
    /**
     * @param Context $context
     * @param ItemFactory $itemFactory
     * @param RatingFactory $ratingFactory
     */
    public function __construct(
        Context $context,
        protected ItemFactory $itemFactory,
        protected RatingFactory $ratingFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Returns the HTML of a review.
     *
     * @param AbstractBlock $block the block that renders the review
     * @param array $review rating, name, comment and date
     * @param string $templateName
     * @phpstan-param array<string, mixed> $review
     *
     * @return string
     */
    public function getItemHtml(
        AbstractBlock $block,
        array $review,
        string $templateName = 'Infrangible_ETrusted::widget/reviews/item.phtml'
    ): string {
        $itemBlock = $this->itemFactory->create();

        try {
            $itemBlock->setLayout($block->getLayout());
        } catch (LocalizedException $exception) {
            $this->_logger->error($exception);

            return '';
        }

        $itemBlock->setTemplate($templateName);
        $itemBlock->setReview($review);

        return $itemBlock->toHtml();
    }

    /**
     * Returns the HTML of the rating of a review.
     *
     * @param AbstractBlock $block the block that renders the rating
     * @param array $review rating, name, comment and date
     * @param string $templateName
     * @phpstan-param array<string, mixed> $review
     *
     * @return string
     */
    public function getRatingHtml(
        AbstractBlock $block,
        array $review,
        string $templateName = 'Infrangible_ETrusted::widget/reviews/item/rating.phtml'
    ): string {
        $rating = $review[ 'rating' ] ?? null;

        $ratingBlock = $this->ratingFactory->create();

        try {
            $ratingBlock->setLayout($block->getLayout());
        } catch (LocalizedException $exception) {
            $this->_logger->error($exception);

            return '';
        }

        $ratingBlock->setTemplate($templateName);
        $ratingBlock->setRating(is_numeric($rating) ? (float)$rating : 0.0);

        return $ratingBlock->toHtml();
    }
}
