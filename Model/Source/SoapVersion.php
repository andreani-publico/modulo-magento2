<?php

namespace Ids\Andreani\Model\Source;

/**
 * Class SoapVersion
 *
 * @description source para elegir la versión del SOAP a utilizar para la conexión con el WS.
 * @author Jhonattan Campo <jcampo@ids.net.ar>
 * @package Ids\Andreani\Model\Source
 */
class SoapVersion implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            'SOAP_1_1' =>SOAP_1_1,
            'SOAP_1_2' =>SOAP_1_2
        ];
    }
}
