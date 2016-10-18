<?php

namespace Ids\Andreani\Controller\Adminhtml\Tarifa;

/**
 * Class Admin
 *
 * @description Action para administrar tarifas de envío Andreani
 *
 * @author Mauro Maximiliano Martinez <mmartinez@ids.net.ar>
 * @package Ids\Andreani\Controller\Adminhtml\Tarifa
 */
class Save extends  \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Ids\Andreani\Model\TarifaFactory
     */
    protected $_tarifaFactory;

    /**
     * @var \Magento\Backend\App\Action\Context
     */
    protected $_context;

    /**
     * Admin constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Ids\Andreani\Model\TarifaFactory $tarifaFactory
    ) {
        $this->_resultPageFactory   = $resultPageFactory;
        $this->_tarifaFactory       = $tarifaFactory;
        $this->_context             = $context;

        parent::__construct($context);
    }

    public function execute()
    {
        $request = $this->_context->getRequest();

        if(($tarifasPost = $request->getParam('tarifa')) && $request->isPost())
        {
            $tarifas = $this->_tarifaFactory->create()
                ->getCollection();

            foreach ($tarifas as $_tarifa)
            {
                foreach ($tarifasPost as $_tarifaPost => $valor)
                {
                    if($_tarifaPost == $_tarifa->getTarifaId())
                    {
                        $_tarifa->setValorEstandar($valor['valor_estandar']);
                        $_tarifa->setValorUrgente($valor['valor_urgente']);
                        $_tarifa->setValorSucursal($valor['valor_sucursal']);
                        $_tarifa->save();
                    }
                }

            }

            $this->messageManager->addSuccessMessage(__('Row data has been successfully saved.'));
            $this->_redirect('andreani/tarifa/admin');
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ids_Andreani::tarifa_save');
    }

}