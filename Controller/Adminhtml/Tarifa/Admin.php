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
class Admin extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * Admin constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory   = $resultPageFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ids_Andreani::tarifa_admin');
    }

}