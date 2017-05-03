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
        $shippingAddress        = $order->getShippingAddress();
        $billingAddress         = $order->getBillingAddress();

        $metodoEnvio = explode('_',$order->getShippingMethod());

        if($metodoEnvio[0] ==  \Ids\Andreani\Model\Carrier\AndreaniSucursal::CARRIER_CODE)
        {
            $order->setCodigoSucursalAndreani($codigoSucursalAndreani);
        }

        /**
         * Parche para hacer que se guarde la altura, piso, departamento, dni, celular y observaciones en el billing
         * cuando el usuario es invitado. Esto funciona haciendo que la direccion de envio y facturacion sean las mismas,
         * ya que sino los datos no coinciden.
         */
        $billingAddress
            ->setFirstname($shippingAddress->getFirstname())
            ->setLastname($shippingAddress->getLastname())
            ->setCompany($shippingAddress->getCompany())
            ->setCity($shippingAddress->getCity())
            ->setRegionId($shippingAddress->getRegionId())
            ->setRegion($shippingAddress->getRegion())
            ->setPostcode($shippingAddress->getPostcode())
            ->setCountryId($shippingAddress->getCountryId())
            ->setTelephone($shippingAddress->getTelephone())
            ->setDni($shippingAddress->getDni())
            ->setAltura($shippingAddress->getAltura())
            ->setPiso($shippingAddress->getPiso())
            ->setDepartamento($shippingAddress->getDepartamento())
            ->setObservaciones($shippingAddress->getObservaciones())
            ->setCelular($shippingAddress->getCelular())
            ->save();

        $order->setCustomerDni($shippingAddress->getDni());

        return $this;
    }
}
