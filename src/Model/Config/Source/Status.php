<?php

declare(strict_types=1);

namespace Infrangible\ETrusted\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Status
    implements OptionSourceInterface
{
    /**
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
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
