<?php

declare(strict_types=1);

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2026 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */

namespace Infrangible\ETrusted\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Phrase;

/**
 * The periods of the aggregate rating.
 */
class Days implements OptionSourceInterface
{
    /**
     * Returns the options.
     *
     * @return array<int, array{value: string, label: Phrase|string}>
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'overall', 'label' => __('Overall')],
            ['value' => '7days', 'label' => __('7 Days')],
            ['value' => '30days', 'label' => __('30 Days')],
            ['value' => '90days', 'label' => __('90 Days')],
            ['value' => '365days', 'label' => __('365 Days')]
        ];
    }
}
