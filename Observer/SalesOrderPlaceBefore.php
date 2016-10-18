<?php
namespace Ids\Andreani\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class SalesEventQuoteSubmitBeforeObserver
 * @package Ids\Andreani\Observer
 */
class SalesOrderPlaceBefore implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * SalesOrderPlaceBefore constructor.
     * @param \Magento\Customer\Model\Session $customer
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Customer\Model\Session $customer,
        \Magento\Checkout\Model\Session $checkoutSession
    )
    {
        $this->_customerSession = $customer;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Graba en la orden el numero de sucursal andreani que tenga el quote, y el dni en el quote address
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $codigoSucursalAndreani = $this->_checkoutSession->getCodigoSucursalAndreani();
        $customer               = $this->_customerSession->getCustomer();
        $order                  = $observer->getEvent()->getOrder();

        $metodoEnvio = explode('_',$order->getShippingMethod());

        if($metodoEnvio[0] ==  \Ids\Andreani\Model\Carrier\AndreaniSucursal::CARRIER_CODE)
        {
            $order->setCodigoSucursalAndreani($codigoSucursalAndreani);
        }

        $order->setCustomerDni($customer->getDni());

        return $this;
    }
}
