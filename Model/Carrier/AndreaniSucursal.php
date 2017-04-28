<?php

namespace Ids\Andreani\Model\Carrier;

use Ids\Andreani\Model\Webservice;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Ids\Andreani\Helper\Data as AndreaniHelper;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Psr\Log\LoggerInterface;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Framework\Xml\Security;
use Magento\Checkout\Model\Session;

class AndreaniSucursal extends AbstractCarrierOnline implements CarrierInterface
{
    const CARRIER_CODE = 'andreanisucursal';

    /**
     * @var string
     */
    protected $_code = self::CARRIER_CODE;

    /**
     * @var Webservice
     */
    protected $_webService;

    /**
     * @var AndreaniHelper
     */
    protected $_andreaniHelper;

    /**
     * @var RateRequest
     */
    protected $_rateRequest;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var CarrierParams
     */
    protected $_carrierParams;

    /**
     * @var Result
     */
    protected $_result;

    /**
     * AndreaniSucursal constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param Security $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory
     * @param ResultFactory $rateFactory
     * @param MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Directory\Helper\Data $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param AndreaniHelper $andreaniHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        AndreaniHelper $andreaniHelper,
        Webservice $webservice,
        Session $checkoutSession,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_andreaniHelper    = $andreaniHelper;
        $this->_webService        = $webservice;
        $this->_checkoutSession   = $checkoutSession;
        $this->_carrierParams     = [];

        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );
    }

    /**
     *
     */
    public function isTrackingAvailable()
    {
        return true;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['sucursal' => $this->getConfigData('title')];
    }

    /**
     * @param RateRequest $request
     * @return bool|Result
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active'))
        {
            return false;
        }

        $result = $this->_rateResultFactory->create();
        $method = $this->_rateMethodFactory->create();

        $helper = $this->_andreaniHelper ;

        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod('sucursal');

        $pesoTotal  = $request->getPackageWeight() * 1000;

        if($pesoTotal > (int)$helper->getPesoMaximo())
        {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage(__('Su pedido supera el peso máximo permitido por Andreani. Por favor divida su orden en más pedidos o consulte al administrador de la tienda.'));

            return $error;
        }

        /**
         * Cuando selecciono el metodo de envio y le doy al boton siguiente en el checkout, vuelve a pasar por aca para
         * recargar actualizar el quote. Hay que buscar la manera de que le llegue un parametro con la cotizacion
         *
         */
        $checkoutSession = $this->_checkoutSession;

        if($request->getFreeShipping() === true)
            $checkoutSession->setFreeShipping(true);
        else
            $checkoutSession->setFreeShipping(false);

        if($nombreSucursal = $checkoutSession->getNombreAndreaniSucursal())
        {
            $method->setMethodTitle($nombreSucursal);
        }
        else
        {
            $method->setMethodTitle($this->getConfigData('description'));
        }

        if($valorCotizacion = $checkoutSession->getCotizacionAndreaniSucursal())
        {
            $method->setPrice($valorCotizacion);
            $method->setCost($valorCotizacion);
        }
        else
        {
            $method->setPrice(0);
            $method->setCost(0);
        }

        $result->append($method);

        return $result;
    }


    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     * @throws \Exception
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $this->_prepareShipmentRequest($request);
        $result = new \Magento\Framework\DataObject();

        //llamar al webservice y ver como se genera la guia con los carrier que vienen en magento. Esto salio del Carrier.php de ups

        $helper = $this->_andreaniHelper;
        $webservice = $this->_webService;

        $order          = $request->getOrderShipment()->getOrder();
        $packageParams  = $request->getPackageParams();

        $volumen        = 0;
        $productName    = '';
        foreach($request->getPackageItems() as $_item)
        {
            $_producto      = $helper->getLoadProduct($_item['product_id']);
            $volumen        += (int) $_producto->getResource()->getAttributeRawValue($_producto->getId(),'volumen',$_producto->getStoreId()) * $_item['qty'];
            $productName    .= $_item['name'].', ';

        }

        $productName    = rtrim(trim($productName),",");
        $pesoTotal      = $packageParams->getWeight() * 1000;

        $carrierParams                                  = $this->_carrierParams;
        $carrierParams['provincia']                     = $request->getRecipientAddressStateOrProvinceCode();
        $carrierParams['localidad']                     = $request->getRecipientAddressCity();
        $carrierParams['codigopostal']                  = $request->getRecipientAddressPostalCode();
        $carrierParams['calle']                         = $request->getRecipientAddressStreet();
        $carrierParams['numero']                        = $order->getShippingAddress()->getAltura()? $order->getShippingAddress()->getAltura() : '';
        $carrierParams['piso']                          = $order->getShippingAddress()->getPiso()? $order->getShippingAddress()->getPiso() : '';
        $carrierParams['departamento']                  = $order->getShippingAddress()->getDepartamento()? $order->getShippingAddress()->getDepartamento() : '';
        $carrierParams['nombre']                        = $order->getShippingAddress()->getFirstname();
        $carrierParams['apellido']                      = $order->getShippingAddress()->getLastname();
        $carrierParams['nombrealternativo']             = '';
        $carrierParams['apellidoalternativo']           = '';
        $carrierParams['tipodedocumento']               = 'DNI';
        $carrierParams['numerodedocumento']             = $order->getShippingAddress()->getDni()? $order->getShippingAddress()->getDni() : '';
        $carrierParams['email']                         = $order->getCustomerEmail();
        $carrierParams['telefonofijo']                  = $request->getRecipientContactPhoneNumber();
        $carrierParams['telefonocelular']               = $order->getShippingAddress()->getCelular()? $order->getShippingAddress()->getCelular() : '';
        $carrierParams['categoriapeso']                 = 1;//TODO próximos versiones implementación de acuerdo a la lógica de  negocio
        $carrierParams['peso']                          = $pesoTotal;
        $carrierParams['detalledeproductosaentregar']   = $productName;
        $carrierParams['detalledeproductosaretirar']    = $productName;
        $carrierParams['volumen']                       = $volumen;
        $carrierParams['valordeclaradoconiva']          = $packageParams->getCustomsValue();
        $carrierParams['idcliente']                     = '';
        $carrierParams['sucursalderetiro']              = $order->getCodigoSucursalAndreani()? $order->getCodigoSucursalAndreani() : '';
        $carrierParams['sucursaldelcliente']            = '';
        $carrierParams['increment_id']                  = $order->getIncrementId();

        $dataGuia = $webservice->GenerarEnviosDeEntregaYRetiroConDatosDeImpresion($carrierParams,$this->_code);
        $response = [];

        if (!$dataGuia) {
            $result->setErrors('Hubo un error al generar el envío');
            return $result;
        } else {
            $shipmentOrderId        = $request->getOrderShipment()->getEntityId();
            $shipmentOrder          = $request->getOrderShipment()->load($shipmentOrderId);
            $shippingLabelContent   = $dataGuia['lastrequest'] ;
            $trackingNumber         = $dataGuia['datosguia']->GenerarEnviosDeEntregaYRetiroConDatosDeImpresionResult->NumeroAndreani;
            $response['tracking_number']        = $trackingNumber;
            $response['shipping_label_content'] = $shippingLabelContent;
            $serialJson 				        = serialize(json_encode($dataGuia));
            $shipmentOrder->setData('andreani_datos_guia',$serialJson);

            return $this->_sendShipmentAcceptRequest($response);
        }
    }

    /**
     * @param $shipmentConfirmResponse
     * @return \Magento\Framework\DataObject
     */
    protected function _sendShipmentAcceptRequest($shipmentConfirmResponse)
    {
        $this->_getAndreaniTracking($shipmentConfirmResponse['tracking_number']);
        $result = new \Magento\Framework\DataObject();
        $result->setShippingLabelContent(base64_decode($shipmentConfirmResponse['tracking_number']));
        $result->setTrackingNumber($shipmentConfirmResponse['tracking_number']);
        return $result;
    }
    /**
     * Processing additional validation to check if carrier applicable.
     *
     * @param \Magento\Framework\DataObject $request
     * @return $this|bool|\Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function proccessAdditionalValidation(\Magento\Framework\DataObject $request)
    {
        $andreaniHelper = $this->_andreaniHelper;

        //Skip by item validation if there is no items in request
        if (!count($this->getAllItems($request)))
        {
            return $this;
        }

        $pesoErrorMsg             = 'Su pedido supera el peso máximo permitido por Andreani. Por favor divida su orden en más pedidos o consulte al administrador de la tienda. Gracias y disculpe las molestias.';
        $datosIncompletosErrorMsg = 'Completá los datos de envío para poder calcular el costo de su pedido.';

        $pesoMaximo  = $andreaniHelper->getPesoMaximo();

        $errorMsg = '';

        /**
         * Mostrar el metodo de envío cuando no este disponible por validaciones erroneas
         */
        $showMethod = $this->getConfigData('showmethod');

        /** @var $item \Magento\Quote\Model\Quote\Item **/
        foreach ($this->getAllItems($request) as $item)
        {
            $product = $item->getProduct();
            if ($product && $product->getId())
            {
                $weight = $product->getWeight();
                $stockItemData = $this->stockRegistry->getStockItem(
                    $product->getId(),
                    $item->getStore()->getWebsiteId()
                );
                $doValidation = true;

                if ($stockItemData->getIsQtyDecimal() && $stockItemData->getIsDecimalDivided()) {
                    if ($stockItemData->getEnableQtyIncrements() && $stockItemData->getQtyIncrements()
                    ) {
                        $weight = $weight * $stockItemData->getQtyIncrements();
                    } else {
                        $doValidation = false;
                    }
                } elseif ($stockItemData->getIsQtyDecimal() && !$stockItemData->getIsDecimalDivided()) {
                    $weight = $weight * $item->getQty();
                }

                if ($doValidation && $weight > $pesoMaximo) {
                    $errorMsg = $pesoErrorMsg;
                    break;
                }
            }
        }

        if (!$request->getDestPostcode() && $this->isZipCodeRequired($request->getDestCountryId()))
        {
            $errorMsg = $datosIncompletosErrorMsg; //__('This shipping method is not available. Please specify the zip code.');
        }

        if ($errorMsg && $showMethod)
        {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($errorMsg);

            return $error;
        } elseif ($errorMsg) {
            return false;
        }

        return $this;
    }


    /**
     * @param $trackings
     * @return Result
     */
    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        $this->_getAndreaniTracking($trackings);
        return $this->_result;
    }



    /**
     * @param $tracking
     * @return mixed
     */
    protected function _getAndreaniTracking($trackings)
    {
        $result = $this->_trackFactory->create();

        if(is_array($trackings))
        {
            foreach ($trackings as $tracking) {
                $status = $this->_trackStatusFactory->create();
                $status->setCarrier($this->getCarrierCode());
                $status->setCarrierTitle($this->getConfigData('title'));
                $status->setTracking($tracking);
                $status->setPopup(1);
                $status->setUrl(
                    $this->_andreaniHelper->getTrackingUrl($tracking)
                );
                $result->append($status);
            }
        }
        elseif(is_string($trackings))
        {
            $status = $this->_trackStatusFactory->create();
            $status->setCarrier($this->getCarrierCode());
            $status->setCarrierTitle($this->getConfigData('title'));
            $status->setTracking($trackings);
            $status->setPopup(1);
            $status->setUrl(
                $this->_andreaniHelper->getTrackingUrl($trackings)
            );
            $result->append($status);
        }

        $this->_result = $result;

        return $this->_result;
    }

}