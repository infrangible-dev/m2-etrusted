<?php

declare(strict_types=1);

namespace Infrangible\ETrusted\Block\Widget;

use FeWeDev\Base\Arrays;
use FeWeDev\Base\Variables;
use Infrangible\ETrusted\Helper\Block;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Reviews extends Template implements BlockInterface
{
    /** @var TimezoneInterface */
    protected $dateTime;

    /** @var \Infrangible\ETrusted\Model\Reviews */
    protected $reviews;

    /** @var Arrays */
    protected $arrays;

    /** @var Variables */
    protected $variables;

    /** @var Block */
    protected $blockHelper;

    private $reviewData = [];

    public function __construct(
        Template\Context $context,
        TimezoneInterface $dateTime,
        \Infrangible\ETrusted\Model\Reviews $reviews,
        Arrays $arrays,
        Variables $variables,
        Block $blockHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );

        $this->dateTime = $dateTime;
        $this->reviews = $reviews;
        $this->arrays = $arrays;
        $this->variables = $variables;
        $this->blockHelper = $blockHelper;
    }

    protected function _construct(): void
    {
        $this->setTemplate($this->getTemplateName());

        parent::_construct();
    }

    public function getTemplateName(): string
    {
        return 'Infrangible_ETrusted::widget/reviews.phtml';
    }

    protected function _toHtml(): string
    {
        $this->reviews->setCount((int)$this->getData('count'));

        $rating = $this->getData('rating');

        if (! $this->variables->isEmpty($rating)) {
            $this->reviews->setRatings(
                explode(
                    ',',
                    $rating
                )
            );
        }

        $status = $this->getData('status');

        if (! $this->variables->isEmpty($status)) {
            $this->reviews->setStatus(
                explode(
                    ',',
                    $status
                )
            );
        }

        $type = $this->getData('review_type');

        if (! $this->variables->isEmpty($type)) {
            $this->reviews->setTypes(
                explode(
                    ',',
                    $type
                )
            );
        }

        $this->reviewData = $this->reviews->get();

        return parent::_toHtml();
    }

    public function getReviews(): array
    {
        $reviews = [];

        foreach ($this->arrays->getValue(
            $this->reviewData,
            'items',
            []
        ) as $item) {
            $reviews[] = [
                'rating'  => $this->arrays->getValue(
                    $item,
                    'rating'
                ),
                'name'    => __('Satisfied Customer'),
                'comment' => $this->arrays->getValue(
                    $item,
                    'comment'
                ),
                'date'    => $this->dateTime->formatDate(
                    date_create(
                        $this->arrays->getValue(
                            $item,
                            'editedAt'
                        )
                    )
                )
            ];
        }

        return $reviews;
    }

    public function getItemHtml(
        array $review,
        string $templateName = 'Infrangible_ETrusted::widget/reviews/item.phtml'
    ): string {
        return $this->blockHelper->getItemHtml(
            $this,
            $review,
            $templateName
        );
    }
}
