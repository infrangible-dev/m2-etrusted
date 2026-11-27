<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\ETrusted\Block\Widget;

use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Stores;
use Infrangible\ETrusted\Model\AggregateRating as AggregateRatingModel;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

/**
 * Widget with the aggregate rating of the service reviews in the period of the widget parameter "days"; shows nothing
 * without reviews (also if eTrusted is not configured or not available).
 */
class AggregateRating extends Template implements BlockInterface
{
    /** @var string */
    protected $_template = 'Infrangible_ETrusted::widget/aggregate_rating.phtml';

    /** @var array<int|string, mixed>|null the aggregate rating of the period, once loaded */
    private ?array $periodData = null;

    /**
     * @param Template\Context $context
     * @param Stores $storeHelper
     * @param AggregateRatingModel $aggregateRating
     * @param Variables $variables
     * @param array $data
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        Template\Context $context,
        protected Stores $storeHelper,
        protected AggregateRatingModel $aggregateRating,
        protected Variables $variables,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Renders the widget if the period has reviews.
     *
     * @return string
     */
    protected function _toHtml(): string
    {
        return $this->getCount() > 0 ? parent::_toHtml() : '';
    }

    /**
     * Returns the period of the widget parameter "days", "overall" by default.
     *
     * @return string
     */
    public function getDays(): string
    {
        return $this->variables->stringValueOrDefault($this->getData('days'), 'overall');
    }

    /**
     * Returns the count of reviews in the period.
     *
     * @return int
     */
    public function getCount(): int
    {
        $count = $this->getPeriodData()[ 'count' ] ?? null;

        return is_numeric($count) ? $this->variables->intValue($count) : 0;
    }

    /**
     * Returns the average rating in the period, formatted with one decimal.
     *
     * @return string
     */
    public function getRating(): string
    {
        $rating = $this->getPeriodData()[ 'rating' ] ?? null;

        return $this->storeHelper->formatNumber(is_numeric($rating) ? (float)$rating : 0.0, 1);
    }

    /**
     * Returns the highest possible rating, formatted with one decimal.
     *
     * @return string
     */
    public function getMaxRating(): string
    {
        return $this->storeHelper->formatNumber(5, 1);
    }

    /**
     * Returns the aggregate rating of the period.
     *
     * @return array
     * @phpstan-return array<int|string, mixed>
     */
    private function getPeriodData(): array
    {
        if ($this->periodData === null) {
            $periodData = $this->aggregateRating->getAggregateRating()[ $this->getDays() ] ?? [];

            $this->periodData = is_array($periodData) ? $periodData : [];
        }

        return $this->periodData;
    }
}
