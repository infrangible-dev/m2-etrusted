<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\ETrusted\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * The ratings of reviews.
 */
class Rating implements OptionSourceInterface
{
    /**
     * Returns the options.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '1', 'label' => '1'],
            ['value' => '2', 'label' => '2'],
            ['value' => '3', 'label' => '3'],
            ['value' => '4', 'label' => '4'],
            ['value' => '5', 'label' => '5']
        ];
    }
}
