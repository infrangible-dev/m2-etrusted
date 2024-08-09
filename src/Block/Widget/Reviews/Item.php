<?php

declare(strict_types=1);

namespace Infrangible\ETrusted\Block\Widget\Reviews;

use Infrangible\ETrusted\Block\Widget\Reviews\Item\Rating;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Item
    extends Template
{
    private $review = [];

    protected function _construct(): void
    {
        $this->setTemplate($this->getTemplateName());

        parent::_construct();
    }

    public function getTemplateName(): string
    {
        return 'Infrangible_ETrusted::widget/reviews/item.phtml';
    }

    public function getReview(): array
    {
        return $this->review;
    }

    public function setReview(array $review): void
    {
        $this->review = $review;
    }

    public function getRatingHtml(array $review): string
    {
        try {
            /** @var Rating $ratingBlock */
            $ratingBlock = $this->getLayout()->createBlock(Rating::class);

            $ratingBlock->setRating($review['rating']);

            return $ratingBlock->toHtml();
        } catch (LocalizedException $exception) {
            $this->_logger->error($exception);

            return '';
        }
    }
}
