<?php

declare(strict_types=1);

namespace Infrangible\ETrusted\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Days implements OptionSourceInterface
{
    /**
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
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
