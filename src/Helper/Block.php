<?php

declare(strict_types=1);

namespace Infrangible\ETrusted\Helper;

use Infrangible\ETrusted\Block\Widget\Reviews\Item;
use Infrangible\ETrusted\Block\Widget\Reviews\Item\Rating;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Block extends AbstractHelper
{
    public function getItemHtml(
        AbstractBlock $block,
        array $review,
        string $templateName = 'Infrangible_ETrusted::widget/reviews/item.phtml'
    ): string {
        try {
            /** @var Item $itemBlock */
            $itemBlock = $block->getLayout()->createBlock(Item::class);

            $itemBlock->setTemplate($templateName);
            $itemBlock->setReview($review);

            return $itemBlock->toHtml();
        } catch (LocalizedException $exception) {
            $this->_logger->error($exception);

            return '';
        }
    }

    public function getRatingHtml(
        AbstractBlock $block,
        array $review,
        string $templateName = 'Infrangible_ETrusted::widget/reviews/item/rating.phtml'
    ): string {
        try {
            /** @var Rating $ratingBlock */
            $ratingBlock = $block->getLayout()->createBlock(Rating::class);

            $ratingBlock->setTemplate($templateName);
            $ratingBlock->setRating($review[ 'rating' ]);

            return $ratingBlock->toHtml();
        } catch (LocalizedException $exception) {
            $this->_logger->error($exception);

            return '';
        }
    }
}
