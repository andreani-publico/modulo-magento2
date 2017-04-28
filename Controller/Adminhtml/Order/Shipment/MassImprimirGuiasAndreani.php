<?php

/**
 * Author: Jhonattan Campo <jcampo@ids.net.ar>
 * Date: 28/09/16
 */
namespace Ids\Andreani\Controller\Adminhtml\Order\Shipment;

ini_set('max_execution_time',3000);

use Magento\Backend\App\Action;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Webapi\Exception;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Backend\App\Action\Context;
use Magento\Shipping\Model\Shipping\LabelGenerator;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory as ShipmentCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Order;
use Ids\Andreani\Model\GuiasMasivas;
use Magento\Framework\Controller\Result\JsonFactory;
use Ids\Andreani\Helper\Data as AndreaniHelper;
use Ids\Andreani\Helper\Email as AndreaniEmailHelper;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MassImprimirGuiasAndreani extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @var LabelGenerator
     */
    protected $labelGenerator;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var ShipmentCollectionFactory
     */
    protected $shipmentCollectionFactory;

    /**
     * @var ShipmentCollectionFactory
     */
    protected $_order;

    /**
     * @var ShipmentCollectionFactory
     */
    protected $_guiasMasivas;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var AndreaniHelper
     */
    protected $_andreaniHelper;

    /**
     * @var AndreaniEmailHelper
     */
    protected $_andreaniEmailHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * MassImprimirGuiasAndreani constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param FileFactory $fileFactory
     * @param LabelGenerator $labelGenerator
     * @param ShipmentCollectionFactory $shipmentCollectionFactory
     * @param Order $order
     * @param GuiasMasivas $guiasMasivas
     * @param JsonFactory $resultJsonFactory
     * @param AndreaniHelper $andreaniHelper
     * @param AndreaniEmailHelper $andreaniEmailHelper
     * @param LoggerInterface $loggerInterface
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        FileFactory $fileFactory,
        LabelGenerator $labelGenerator,
        ShipmentCollectionFactory $shipmentCollectionFactory,
        Order $order,
        GuiasMasivas $guiasMasivas,
        JsonFactory $resultJsonFactory,
        AndreaniHelper $andreaniHelper,
        AndreaniEmailHelper $andreaniEmailHelper,
        LoggerInterface $loggerInterface
    ) {
        $this->fileFactory                  = $fileFactory;
        $this->collectionFactory            = $collectionFactory;
        $this->shipmentCollectionFactory    = $shipmentCollectionFactory;
        $this->labelGenerator               = $labelGenerator;
        $this->_order                       = $order;
        $this->_guiasMasivas                = $guiasMasivas;
        $this->_resultJsonFactory           = $resultJsonFactory;
        $this->_andreaniHelper              = $andreaniHelper;
        $this->_andreaniEmailHelper         = $andreaniEmailHelper;
        $this->logger                       = $loggerInterface;

        parent::__construct($context, $filter);
    }

    /**
     * @param AbstractCollection $collection
     * @return $this
     */
    protected function massAction(AbstractCollection $collection)
    {
        $helper = $this->_andreaniHelper;

        if($helper->getGeneracionGuiasConfig() == '1')
        {
            $this->_prepareGuiaByOrder($collection);
        }
        else
        {
            $this->_prepareGuiaByItem($collection);
        }

        $resultRedirect = $this->resultRedirectFactory->create()->setPath('andreani/guias/admin');
        return $resultRedirect;
    }

    /**
     * @description Prepara la guía para ser generada por ítems contenidos
     * en la orden.
     * @param AbstractCollection $collection
     */
    protected function _prepareGuiaByItem(AbstractCollection $collection)
    {
        $guiasContent = [];
        if (count($collection->getAllIds()))
        {
            /** @var \Magento\Sales\Model\Order\Shipment $shipment */
            $ordersIds = $collection->getAllIds();

            /**
             * Carga cada orden y se cerciora que pertenezca a algún
             * carrier de Andreani.
             */
            foreach ($ordersIds as $orderId)
            {
                $order = $this->_order->load($orderId);
                if(
                    $order->getShippingMethod()=='andreaniestandar_estandar' ||
                    $order->getShippingMethod()=='andreaniurgente_urgente' ||
                    $order->getShippingMethod()=='andreanisucursal_sucursal'
                )
                {
                    /**
                     * valida si tiene items por enviar.
                     *
                     */
                    $leftShipments = false;
                    foreach($order->getAllItems() AS $key => $orderItem)
                    {
                        if((int)$orderItem->getQtyOrdered() != (int)$orderItem->getQtyShipped())
                        {
                            $leftShipments = true;
                            break;
                        }
                    }
                }
                else
                {
                    if($collection->getSize()>1)
                    {
                        $this->messageManager->addNoticeMessage(__('Alguna de Las órdenes seleccionadas no tienen envíos con Andreani'));
                    }
                    else
                    {
                        $this->messageManager->addNoticeMessage(__('La orden seleccionada no tiene envíos con Andreani'));
                    }
                    continue;
                }

                if(!$order->hasShipments() || $leftShipments)
                {
                    $this->_guiasMasivas->doShipmentRequest($order->getAllItems());
                }
            }

            /**
             * Arma la colección de shipments para leer el json serializado
             * de la DB.
             */
            $ordersShipment = $this->shipmentCollectionFactory->create()
                ->setOrderFilter(['in' => $collection->getAllIds()]);

            if ($ordersShipment->getSize()) {
                foreach ($ordersShipment as $order) {
                    $guiaContent = $order->getAndreaniDatosGuia();
                    if ($guiaContent) {
                        $guiasContent[$order->getIncrementId()] = json_decode(unserialize($guiaContent));
                    }
                }
            }
            try
            {
                /**
                 * si el contenido de las guías no está vacío
                 */
                if (!empty($guiasContent)) {
                    $this->_generarGuiasMasivas($guiasContent,$collection->getAllIds());
                }
                //return true;
            }
            catch (Exception $e)
            {
                $this->messageManager->addErrorMessage(__('No hay guías creadas para las órdenes seleccionadas.'));
                $this->logger->error($e->getMessage());
            }
        }
    }

    /**
     * @description Prepara la guía para ser generada.
     * @param AbstractCollection $collection
     */
    protected function _prepareGuiaByOrder(AbstractCollection $collection)
    {
        $guiasContent = [];
        if (count($collection->getAllIds()))
        {
            $orderIdsSinAndreani = [];

            $cantidadOrdenes = 0;
            $orderIdsExcluidos = [];

            /**
             * Carga cada orden y se cerciora que pertenezca a algún
             * carrier de Andreani.
             */
            foreach ($collection->getItems() as $order)
            {
                if($cantidadOrdenes >= 100)
                {
                    $orderIdsExcluidos[] = $order->getIncrementId();
                    continue;
                }
                if(
                    $order->getShippingMethod()=='andreaniestandar_estandar' ||
                    $order->getShippingMethod()=='andreaniurgente_urgente' ||
                    $order->getShippingMethod()=='andreanisucursal_sucursal'
                )
                {
                    /**
                     * valida si tiene items por enviar.
                     *
                     */
                    $leftShipments = false;
                    foreach($order->getAllItems() AS $key => $orderItem)
                    {
                        if((int)$orderItem->getQtyOrdered() != (int)$orderItem->getQtyShipped())
                        {
                            $leftShipments = true;
                            break;
                        }
                    }
                    /**
                     * Si la orden no tiene envíos, lo genera.
                     */
                    if(!$order->hasShipments() || $leftShipments)
                    {
                        $this->_guiasMasivas->doShipmentRequest($order);
                    }

                    $cantidadOrdenes++;
                }
                else
                {
                    if($collection->getSize()>1)
                    {
                        $orderIdsSinAndreani[] = $order->getIncrementId();
                    }
                    else
                    {
                        $this->messageManager->addNoticeMessage(__('La orden seleccionada no tiene envíos con Andreani.'));
                    }
                    continue;
                }
            }

            if(count($orderIdsExcluidos))
            {
                $this->messageManager->addNoticeMessage('Sólo se puede generar guías para un máximo de 100 pedidos simultáneamente. No se generaron guías para las órdenes: '.implode(',',$orderIdsExcluidos).'.');
            }

            if(count($orderIdsSinAndreani))
            {
                $this->messageManager->addNoticeMessage('Las órdenes '.implode(',',$orderIdsSinAndreani).' no tienen envíos con Andreani');
            }

            /**
             * Arma la colección de shipments para leer el json serializado
             * de la DB.
             */
            $ordersShipment = $this->shipmentCollectionFactory->create()
                ->setOrderFilter(['in' => $collection->getAllIds()]);

            if ($ordersShipment->getSize())
            {
                foreach ($ordersShipment as $order)
                {
                    $guiaContent = $order->getAndreaniDatosGuia();
                    if ($guiaContent) {
                        $guiasContent[$order->getIncrementId()] = json_decode(unserialize($guiaContent));
                    }
                }
            }
        }

        try
        {
            /**
             * si el contenido de las guías no está vacío
             */
            if (!empty($guiasContent))
            {
                $this->_generarGuias($guiasContent,$collection->getAllIds());
            }
        }
        catch (Exception $e)
        {
            $this->messageManager->addErrorMessage(__('No hay guías creadas para las órdenes seleccionadas.'));
        }
    }

    /**
     * @description Genera la guía en PDF cuando recibe como parámetros,
     * el contenido de la misma y los ID de las órdenes.
     * @param $guiasContent
     * @param $ordersIds
     * @throws \Exception
     * @throws \Zend_Pdf_Exception
     */
    protected function _generarGuias($guiasContent,$ordersIds)
    {
        try
        {
            $helper             = $this->_andreaniHelper;
            $order_id = '';

            /**
             * Concatena los ID para mandarlos por comas en la URL.
             */
            foreach($ordersIds AS $key => $orderId)
            {
                $order_id.=$orderId.',';
            }

            $order_id = rtrim($order_id, ',');

            /**
             * Accede al objeto para crear el código de barras.
             */
            foreach($guiasContent AS $key => $guiaData)
            {
                $object = $guiaData->datosguia->GenerarEnviosDeEntregaYRetiroConDatosDeImpresionResult;
                $helper->crearCodigoDeBarras($object->NumeroAndreani);
            }

            $pdfName        = 'guia_masiva_'.date_timestamp_get(date_create());

            /**
             * Crea el bloque dinámicamente y le pasa los parámetros por array para
             * que renderice la guía en html.
             */
            $block = $this->_view
                ->getLayout()
                ->createBlock('Ids\Andreani\Block\Generarhtmlmasivo',
                    "guiamasiva",
                    ['data' => [
                        'order_id' => $order_id
                    ]
                    ])
                ->setData('area', 'frontend')
                ->setTemplate('Ids_Andreani::guiamasiva.phtml');

            $html = $block->toHtml();

            /**
             * Espera recibir "true" después de mandarle al método del helper
             * que se encarga de generar la guía en HTML. El tercer parámetro
             * fuerza la descarga (D) o fuerza el almacenamiento en el filesystem (F)
             */
            if($helper->generateHtml2Pdf($pdfName,$html,'F'))
            {
                $filePath 		= $helper->getGuiaPdfPath($pdfName);
                $andreaniEmailHelper = $this->_andreaniEmailHelper;

                $senderTransEmail   = $helper->getTransEmails('contact_general');
                $receiverTransEmail = $helper->getTransEmails('andreani');

                $receiverInfo = [
                    'name'  => $receiverTransEmail['name'],
                    'email' => $receiverTransEmail['email']

                ];

                $senderInfo = [
                    'name'  => $senderTransEmail['name'],
                    'email' => $senderTransEmail['email'],
                ];

                /**
                 * Asigna el valores a las variables.
                 */
                $emailTemplateVariables                 = [];
                $emailTemplateVariables['pdfName']      = $pdfName;
                $emailTemplateVariables['pdfPath']      = $filePath;


                /**
                 * Notifica por mail que se creó una guía.
                 */
                $andreaniEmailHelper->notificarGuiaGenerada(
                    $emailTemplateVariables,
                    $senderInfo,
                    $receiverInfo
                );
                $this->messageManager->addSuccess( __('La guía se generó correctamente.') );

                foreach($guiasContent AS $key => $guiaData)
                {
                    $object  = $guiaData->datosguia->GenerarEnviosDeEntregaYRetiroConDatosDeImpresionResult;
                    unlink($helper->getDirectoryPath('media')."/andreani/".$object->NumeroAndreani.'.png');
                }
            }

        }catch (Exception $e)
        {
            $this->messageManager->addError(__('Hubo un problema generando la guía. Inténtelo de nuevo.'));
            $this->logger->error($e->getMessage());
        }
    }


    /**
     * @description Genera masivamente las guías en un solo PDF
     * @param $guiasContent
     * @param $ordersIds
     */
    protected function _generarGuiasMasivas($guiasContent,$ordersIds)
    {
        try
        {
            $helper                 = $this->_andreaniHelper;
            $order_id               = '';

            /**
             * Concatena los ID para mandarlos por comas en la URL.
             */
            foreach($ordersIds AS $key => $orderId)
            {
                $order_id.=$orderId.',';
            }

            $order_id = rtrim($order_id, ',');

            /**
             * Accede al objeto para crear el código de barras.
             */
            foreach($guiasContent AS $key => $guiaData)
            {
                $object = $guiaData->datosguia->GenerarEnviosDeEntregaYRetiroConDatosDeImpresionResult;
                $helper->crearCodigoDeBarras($object->NumeroAndreani);
            }

            /**
             * Instancia la url de la tienda que posteriormente recibirá los parámetros para armar la guía.
             */
            $pdfName        = 'guia_masiva_'.date_timestamp_get(date_create());

            /**
             * Crea el bloque dinámicamente y le pasa los parámetros por array para
             * que renderice la guía en html.
             */
            $block = $this->_view
                ->getLayout()
                ->createBlock('Ids\Andreani\Block\Generarhtmlmasivo',
                    "guiamasiva",
                    ['data' => [
                        'order_id' => $order_id
                    ]
                    ])
                ->setData('area', 'frontend')
                ->setTemplate('Ids_Andreani::guiamasiva.phtml');

            $html = $block->toHtml();

            /**
             * Espera recibir "true" después de mandarle al método del helper
             * que se encarga de generar la guía en HTML. El tercer parámetro
             * fuerza la descarga (D) o fuerza el almacenamiento en el filesystem (F)
             */
            if($helper->generateHtml2Pdf($pdfName,$html,'F'))
            {
                $filePath 		= $helper->getGuiaPdfPath($pdfName);
                $andreaniEmailHelper = $this->_andreaniEmailHelper;

                $senderTransEmail   = $helper->getTransEmails('contact_general');
                $receiverTransEmail = $helper->getTransEmails('andreani');

                $receiverInfo = [
                    'name'  => $receiverTransEmail['name'],
                    'email' => $receiverTransEmail['email']

                ];

                $senderInfo = [
                    'name'  => $senderTransEmail['name'],
                    'email' => $senderTransEmail['email'],
                ];

                /**
                 * Asigna el valores a las variables.
                 */
                $emailTemplateVariables                 = [];
                $emailTemplateVariables['pdfName']      = $pdfName;
                $emailTemplateVariables['pdfPath']      = $filePath;

                /**
                 * Notifica por mail que se creó una guía.
                 */
                $andreaniEmailHelper->notificarGuiaGenerada(
                    $emailTemplateVariables,
                    $senderInfo,
                    $receiverInfo
                );
                $this->messageManager->addSuccessMessage(__('La guía se generó correctamente.'));

                foreach($guiasContent AS $key => $guiaData)
                {
                    $object  = $guiaData->datosguia->GenerarEnviosDeEntregaYRetiroConDatosDeImpresionResult;
                    unlink($helper->getDirectoryPath('media')."/andreani/".$object->NumeroAndreani.'.png');
                }
            }
        }
        catch (Exception $e)
        {
            $this->messageManager->addErrorMessage(__('Hubo un problema generando la guía. Inténtelo de nuevo.'));
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Ids_Andreani::guias_admin');
    }
}
