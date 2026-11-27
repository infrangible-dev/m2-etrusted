<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\ETrusted\Block\Widget;

use DateTime;
use Exception;
use FeWeDev\Base\Variables;
use Infrangible\ETrusted\Helper\Block;
use Infrangible\ETrusted\Model\Reviews as ReviewsModel;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

/**
 * Widget with a slider of the latest reviews; the widget parameters "count", "rating", "status" and "review_type"
 * select them. Shows nothing without reviews (also if eTrusted is not configured or not available).
 */
class Reviews extends Template implements BlockInterface
{
    /** @var string */
    protected $_template = 'Infrangible_ETrusted::widget/reviews.phtml';

    /** @var array<int, array{rating: float, name: string, comment: string, date: string}>|null once loaded */
    private ?array $reviews = null;

    /**
     * @param Template\Context $context
     * @param TimezoneInterface $timezone
     * @param ReviewsModel $reviewsModel
     * @param Variables $variables
     * @param Block $blockHelper
     * @param array $data
     * @phpstan-param array<string, mixed> $data
     */
    public function __construct(
        Template\Context $context,
        protected TimezoneInterface $timezone,
        protected ReviewsModel $reviewsModel,
        protected Variables $variables,
        protected Block $blockHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Renders the widget if there are reviews.
     *
     * @return string
     */
    protected function _toHtml(): string
    {
        return count($this->getReviews()) > 0 ? parent::_toHtml() : '';
    }

    /**
     * Returns the reviews with rating (1 to 5), name, comment and the formatted date.
     *
     * @return array<int, array{rating: float, name: string, comment: string, date: string}>
     */
    public function getReviews(): array
    {
        if ($this->reviews === null) {
            $this->reviews = [];

            $items = $this->reviewsModel->getReviews(
                $this->variables->intValue($this->getData('count')),
                $this->getParameterValues('rating'),
                $this->getParameterValues('status'),
                $this->getParameterValues('review_type')
            );

            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $rating = $item[ 'rating' ] ?? null;
                $comment = $item[ 'comment' ] ?? null;

                $this->reviews[] = [
                    'rating'  => is_numeric($rating) ? (float)$rating : 0.0,
                    'name'    => (string)__('Satisfied Customer'),
                    'comment' => is_string($comment) ? $comment : '',
                    'date'    => $this->formatReviewDate($item[ 'editedAt' ] ?? null)
                ];
            }
        }

        return $this->reviews;
    }

    /**
     * Returns the HTML of a review.
     *
     * @param array $review
     * @param string $templateName
     * @phpstan-param array<string, mixed> $review
     *
     * @return string
     */
    public function getItemHtml(
        array $review,
        string $templateName = 'Infrangible_ETrusted::widget/reviews/item.phtml'
    ): string {
        return $this->blockHelper->getItemHtml($this, $review, $templateName);
    }

    /**
     * Returns the values of a multiselect parameter.
     *
     * Widgets in CMS content have a comma-separated list, widget instances (Content > Widgets) may have an array.
     *
     * @param string $name
     *
     * @return string[]
     */
    private function getParameterValues(string $name): array
    {
        $value = $this->getData($name);

        if (is_string($value)) {
            $value = explode(',', $value);
        }

        if (! is_array($value)) {
            return [];
        }

        $values = [];

        foreach ($value as $item) {
            $item = is_scalar($item) ? trim((string)$item) : '';

            if ($item !== '') {
                $values[] = $item;
            }
        }

        return $values;
    }

    /**
     * Returns the date of eTrusted ("2024-05-01T10:00:00.000Z") in the short format of the store.
     *
     * The date is empty if it is missing or invalid.
     *
     * @param mixed $date
     *
     * @return string
     */
    private function formatReviewDate(mixed $date): string
    {
        if (! is_string($date) || $date === '') {
            return '';
        }

        try {
            return $this->timezone->formatDate(new DateTime($date));
        } catch (Exception) {
            return '';
        }
    }
}
