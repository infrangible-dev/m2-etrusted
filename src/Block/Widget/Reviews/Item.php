<?php

declare(strict_types=1);

namespace Infrangible\ETrusted\Block\Widget\Reviews;

use Infrangible\ETrusted\Helper\Block;
use Magento\Framework\View\Element\Template;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Item extends Template
{
    /** @var Block */
    protected $blockHelper;

    private $review = [];

    public function __construct(Template\Context $context, Block $blockHelper, array $data = [])
    {
        parent::__construct(
            $context,
            $data
        );

        $this->blockHelper = $blockHelper;
    }

    public function getReview(): array
    {
        return $this->review;
    }

    public function setReview(array $review): void
    {
        $this->review = $review;
    }

    public function getRatingHtml(
        array $review,
        string $templateName = 'Infrangible_ETrusted::widget/reviews/item/rating.phtml'
    ): string {
        return $this->blockHelper->getRatingHtml(
            $this,
            $review,
            $templateName
        );
    }
}
