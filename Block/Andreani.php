<?php

namespace Ids\Andreani\Block;

use Magento\Framework\View\Element\Template;

/**
 * Class Andreani
 *
 * @description Bloque para renderizar los planes de pago en el checkout
 *
 * @author Mauro Maximiliano Martinez <mmartinez@ids.net.ar>
 * @package Ids\Andreani\Block
 */
class Andreani extends Template
{
    /**
     * @var \Ids\Andreani\Model\ProvinciaFactory
     */
    protected $_provinciaFactory;

    /**
     * Andreani constructor.
     * @param Template\Context $context
     * @param array $data
     * @param \Ids\Andreani\Model\ProvinciaFactory $provinciaFactory
     */
    public function __construct(
        Template\Context $context,
        array $data = [],
        \Ids\Andreani\Model\ProvinciaFactory $provinciaFactory
    )
    {
        $this->_provinciaFactory    = $provinciaFactory;
        $this->_context             = $context;

        parent::__construct($context, $data);
    }

    /**
     * @description Retorna el listado de provincias
     *
     * @return array
     */
    public function getProvincias()
    {
        $provinciasDisponibles = $this->_provinciaFactory
            ->create()
            ->getCollection();
        $provinciasDisponibles->getSelect()->order('nombre ASC');

        return $provinciasDisponibles->getData();
    }

}