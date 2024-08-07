<?php

declare(strict_types=1);

namespace Infrangible\ETrusted\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Type
    implements OptionSourceInterface
{
    /**
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => 'SERVICE_REVIEW', 'label' => __('Service Review')],
            ['value' => 'PRODUCT_REVIEW', 'label' => __('Product Review')]
        ];
    }
}
