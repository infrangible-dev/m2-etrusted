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
 * The status of reviews.
 */
class Status implements OptionSourceInterface
{
    /**
     * Returns the options.
     *
     * @return array<int, array{value: string, label: Phrase|string}>
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'APPROVED', 'label' => __('Approved')],
            ['value' => 'MODERATION', 'label' => __('Moderation')],
            ['value' => 'REJECTED', 'label' => __('Rejected')]
        ];
    }
}
