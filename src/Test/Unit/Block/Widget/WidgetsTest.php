<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\ETrusted\Test\Unit\Block\Widget;

use DateTimeInterface;
use FeWeDev\Base\Variables;
use Infrangible\Core\Helper\Stores;
use Infrangible\ETrusted\Block\Widget\AggregateRating;
use Infrangible\ETrusted\Block\Widget\Reviews;
use Infrangible\ETrusted\Block\Widget\Reviews\Item;
use Infrangible\ETrusted\Block\Widget\Reviews\Item\Rating;
use Infrangible\ETrusted\Helper\Block;
use Infrangible\ETrusted\Model\AggregateRating as AggregateRatingModel;
use Infrangible\ETrusted\Model\Reviews as ReviewsModel;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Element\Template\Context;
use PHPUnit\Framework\TestCase;

/**
 * Tests the widgets: the parameters, the data of eTrusted that they show and the rating stars.
 */
class WidgetsTest extends TestCase
{
    /** @var array<int, array{int, string[], string[], string[]}> the parameters of the requested reviews */
    private array $requests = [];

    /**
     * Returns the context of the blocks.
     *
     * @return Context
     */
    private function createContext(): Context
    {
        $context = $this->createStub(Context::class);
        $context->method('getEventManager')->willReturn($this->createStub(ManagerInterface::class));
        $context->method('getScopeConfig')->willReturn($this->createStub(ScopeConfigInterface::class));

        return $context;
    }

    /**
     * Returns the reviews widget with the parameters and the items of eTrusted.
     *
     * @param array<string, mixed> $parameters
     * @param array<int|string, mixed> $items
     *
     * @return Reviews
     */
    private function createReviews(array $parameters, array $items): Reviews
    {
        $reviewsModel = $this->createStub(ReviewsModel::class);
        $reviewsModel->method('getReviews')->willReturnCallback(
            function (int $count, array $ratings, array $status, array $types) use ($items): array {
                $this->requests[] = [$count, $ratings, $status, $types];

                return $items;
            }
        );

        $timezone = $this->createStub(TimezoneInterface::class);
        $timezone->method('formatDate')->willReturnCallback(
            fn(DateTimeInterface $date): string => $date->format('d.m.Y')
        );

        return new Reviews(
            $this->createContext(),
            $timezone,
            $reviewsModel,
            new Variables(),
            $this->createStub(Block::class),
            $parameters
        );
    }

    /**
     * Tests the parameters: comma-separated lists of widgets in CMS content and arrays of widget instances.
     *
     * @return void
     */
    public function testReviewParameters(): void
    {
        $this->createReviews(['count' => '3', 'rating' => '4,5', 'review_type' => 'SERVICE_REVIEW'], [])->getReviews();
        $this->createReviews(['count' => 5, 'rating' => ['5'], 'status' => ['APPROVED', '']], [])->getReviews();

        $this->assertSame(
            [[3, ['4', '5'], [], ['SERVICE_REVIEW']], [5, ['5'], ['APPROVED'], []]],
            $this->requests
        );
    }

    /**
     * Tests the reviews: invalid ratings, comments and dates of eTrusted give defaults.
     *
     * @return void
     */
    public function testReviews(): void
    {
        $reviews = $this->createReviews(
            ['count' => '2'],
            [
                ['rating' => 5, 'comment' => '<b>Great</b>', 'editedAt' => '2024-05-01T10:00:00.000Z'],
                ['rating' => null, 'comment' => ['text' => 'x'], 'editedAt' => 'yesterday-ish'],
                'invalid'
            ]
        );

        $this->assertSame(
            [
                ['rating' => 5.0, 'name' => 'Satisfied Customer', 'comment' => '<b>Great</b>', 'date' => '01.05.2024'],
                ['rating' => 0.0, 'name' => 'Satisfied Customer', 'comment' => '', 'date' => '']
            ],
            $reviews->getReviews()
        );
        $this->assertSame($reviews->getReviews(), $reviews->getReviews());
        $this->assertCount(1, $this->requests);
    }

    /**
     * Without reviews the widget shows nothing.
     *
     * @return void
     */
    public function testWithoutReviews(): void
    {
        $this->assertSame('', $this->createReviews(['count' => '3'], [])->toHtml());
    }

    /**
     * Tests the aggregate rating of the period of the widget, "overall" by default; without reviews the widget shows
     * nothing.
     *
     * @return void
     */
    public function testAggregateRating(): void
    {
        $model = $this->createStub(AggregateRatingModel::class);
        $model->method('getAggregateRating')->willReturn(
            ['overall' => ['count' => 120, 'rating' => 4.82], '7days' => ['count' => 0, 'rating' => null]]
        );

        $storeHelper = $this->createStub(Stores::class);
        $storeHelper->method('formatNumber')->willReturnCallback(
            fn(float $value, int $precision): string => number_format($value, $precision, ',', '.')
        );

        $widget = new AggregateRating($this->createContext(), $storeHelper, $model, new Variables());

        $this->assertSame('overall', $widget->getDays());
        $this->assertSame(120, $widget->getCount());
        $this->assertSame('4,8', $widget->getRating());
        $this->assertSame('5,0', $widget->getMaxRating());

        $widget = new AggregateRating(
            $this->createContext(),
            $storeHelper,
            $model,
            new Variables(),
            ['days' => '7days']
        );

        $this->assertSame(0, $widget->getCount());
        $this->assertSame('0,0', $widget->getRating());
        $this->assertSame('', $widget->toHtml());
    }

    /**
     * Tests the width of the stars, limited to 0 to 100 percent.
     *
     * @return void
     */
    public function testRatingPercent(): void
    {
        $rating = new Rating($this->createContext());

        foreach ([[4.5, 90], [5.0, 100], [3.33, 67], [7.0, 100], [-1.0, 0]] as [$value, $percent]) {
            $rating->setRating($value);

            $this->assertSame($percent, $rating->getPercent());
        }
    }

    /**
     * Tests the URL of the trust mark image of the configuration; without image the URL is empty.
     *
     * @return void
     */
    public function testTrustMarkUrl(): void
    {
        $trustMark = 'default/trust-mark.png';

        $storeHelper = $this->createStub(Stores::class);
        $storeHelper->method('getStoreConfig')->willReturnCallback(
            function (string $path) use (&$trustMark): ?string {
                return $path === Item::CONFIG_TRUST_MARK ? $trustMark : null;
            }
        );
        $storeHelper->method('getMediaUrl')->willReturn('https://shop.test/media/');

        $item = new Item($this->createContext(), $this->createStub(Block::class), $storeHelper, new Variables());

        $this->assertSame(
            'https://shop.test/media/infrangible/etrusted/default/trust-mark.png',
            $item->getTrustMarkUrl()
        );

        $trustMark = null;

        $this->assertSame('', $item->getTrustMarkUrl());
    }
}
