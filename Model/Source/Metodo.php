<?php

namespace Ids\Andreani\Model\Source;
use Magento\Framework\Option\ArrayInterface;
/**
 * Class Método
 *
 * @description arma el array del método de consulta de sucursales.
 * @author Jhonattan Campo <jcampo@ids.net.ar>
 * @package Ids\Andreani\Model\Source
 */
class Metodo implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            'basico'    => 'Básico',
            'medio'     => 'Medio',
            'completo'  => 'Completo'
        ];
    }
}
