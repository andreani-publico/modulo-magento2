<?php

namespace Ids\Andreani\Model\Source;

/**
 * Class PesoMaximo
 *
 * @description
 * @author
 * @package Ids\Andreani\Model\Source
 */
class PesoMaximo implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            '30000'  => '30 kg',
            '50000'  => '50 kg',
            '100000' => '100 kg'
        ];
    }
}
