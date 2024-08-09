<?php

declare(strict_types=1);

namespace Infrangible\ETrusted\Block\Widget\Reviews\Item;

use Magento\Framework\View\Element\Template;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Rating
    extends Template
{
    /** @var float */
    private $rating;

    protected function _construct(): void
    {
        $this->setTemplate($this->getTemplateName());

        parent::_construct();
    }

    public function getTemplateName(): string
    {
        return 'Infrangible_ETrusted::widget/reviews/item/rating.phtml';
    }

    public function getRating(): float
    {
        return $this->rating;
    }

    public function setRating(float $rating): void
    {
        $this->rating = $rating;
    }
}
