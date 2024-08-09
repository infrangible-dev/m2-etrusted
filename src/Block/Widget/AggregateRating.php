<?php

declare(strict_types=1);

namespace Infrangible\ETrusted\Block\Widget;

use FeWeDev\Base\Arrays;
use Infrangible\Core\Helper\Stores;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class AggregateRating
    extends Template
    implements BlockInterface
{
    /** @var Stores */
    protected $storeHelper;

    /** @var \Infrangible\ETrusted\Model\AggregateRating */
    protected $aggregateRating;

    /** @var Arrays */
    protected $arrays;

    private $aggregateRatingData = [];

    public function __construct(
        Template\Context                            $context,
        Stores                                      $storeHelper,
        \Infrangible\ETrusted\Model\AggregateRating $aggregateRating,
        Arrays                                      $arrays,
        array                                       $data = [])
    {
        parent::__construct($context, $data);

        $this->storeHelper = $storeHelper;
        $this->aggregateRating = $aggregateRating;
        $this->arrays = $arrays;
    }

    protected function _construct(): void
    {
        $this->setTemplate($this->getTemplateName());

        parent::_construct();
    }

    public function getTemplateName(): string
    {
        return 'Infrangible_ETrusted::widget/aggregate_rating.phtml';
    }

    protected function _toHtml(): string
    {
        $data = $this->aggregateRating->get();

        $this->aggregateRatingData = $this->arrays->getValue($data, $this->getData('days'), []);

        return parent::_toHtml();
    }

    public function getCount(): int
    {
        return $this->arrays->getValue($this->aggregateRatingData, 'count', 0);
    }

    public function getRating(): string
    {
        return $this->storeHelper->formatNumber($this->arrays->getValue($this->aggregateRatingData, 'rating', 0), 1);
    }

    public function getMaxRating(): string
    {
        return $this->storeHelper->formatNumber(5, 1);
    }
}
