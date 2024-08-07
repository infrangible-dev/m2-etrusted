<?php

declare(strict_types=1);

namespace Infrangible\ETrusted\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   2014-2024 Softwareentwicklung Andreas Knollmann
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Rating
    implements OptionSourceInterface
{
    /**
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => '1', 'label' => 1],
            ['value' => '2', 'label' => 2],
            ['value' => '3', 'label' => 3],
            ['value' => '4', 'label' => 4],
            ['value' => '5', 'label' => 5]
        ];
    }
}
