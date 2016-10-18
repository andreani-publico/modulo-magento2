<?php

namespace Ids\Andreani\Model\Source;

/**
 * Class Modo
 *
 * @description Opciones customizadas para seleccionar el modo habilitado del metodo de pago.
 * @author
 * @package Ids\Andreani\Model\Source
 */
class Modo implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            \Ids\Andreani\Model\Webservice::MODE_DEV  =>'Desarrollo',
            \Ids\Andreani\Model\Webservice::MODE_PROD =>'Producción'
        ];
    }
}
