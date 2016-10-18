<?php

namespace Ids\Andreani\Model;

use Magento\Framework\Model\AbstractModel;

/**
 * Class CodigoPostal
 *
 * @description Modelo representativo de la tabla ids_andreani_codigo_postal.
 * @author Mauro Maximiliano Martinez <mmartinez@ids.net.ar>
 * @package Ids\Andreani\Model
 */
class CodigoPostal extends AbstractModel
{
    protected $_eventPrefix = 'ids_andreani_codigo_postal';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'andreani_codigo_postal';

    /**
     * True if data changed
     *
     * @var bool
     */
    protected $_isStatusChanged = false;


    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Inicia el resource model
     */
    protected function _construct()
    {
        $this->_init('Ids\Andreani\Model\ResourceModel\CodigoPostal');
    }

}