<?php

namespace Ids\Andreani\Model\ResourceModel;

/**
 * Class Tarifa
 *
 * @description ResourceModel para la tabla Tarifa
 * @author Mauro Maximiliano Martinez <mmartinez@ids.net.ar>
 * @package Ids\Andreani\Model\ResourceModel
 */
class Tarifa extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Construct
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param string|null $resourcePrefix
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
    }

    public function _construct()
    {
        $this->_init('ids_andreani_tarifa','tarifa_id');
    }

}