<?php

namespace Ids\Andreani\Controller\Generarguia;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\TestFramework\Inspection\Exception;
use Ids\Andreani\Helper\Data as AndreaniHelper;
use Magento\Framework\Controller\Result\RawFactory as ResultRawFactory;
use Magento\Framework\App\Response\Http\FileFactory;


/**
 * Class Generarguia
 *
 * @description
 *
 * @author Jhonattan Campo <jcampo@ids.net.ar>
 * @package Ids\Andreani\Controller\Generarguia
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var AndreaniHelper
     */
    protected $_andreaniHelper;
    
    /**
     * @var ResultRawFactory
     */
    protected $_resultRawFactory;

    /**
     * @var FileFactory
     */
    protected $_fileFactory;



    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct
    (
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory,
        AndreaniHelper $andreaniHelper,
        ResultRawFactory $resultRawFactory,
        FileFactory $fileFactory
    )
    {
        $this->_resultPageFactory   = $resultPageFactory;
        $this->_resultJsonFactory   = $resultJsonFactory;
        $this->_andreaniHelper      = $andreaniHelper;
        $this->_resultRawFactory    = $resultRawFactory;
        $this->_fileFactory         = $fileFactory;


        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        //Recibe por parámetro el id de la orden, y manda a la librería
        //todos los datos para que genere el html que será posteriormente la guía en PDF.
        $request            = $this->getRequest();
        $result             = $this->_resultJsonFactory->create();
        $helper             = $this->_andreaniHelper;
        $orderId            = $request->getParam('order_id');
        $order              = $helper->getLoadShipmentOrder($orderId);
        $andreaniDatosGuia  = '';
        $guiasArray         = [];

        if($order->hasShipments())
        {
            $orderShipments = $order->getShipmentsCollection();
            foreach($orderShipments->getData() AS $shipmentData)
            {
                $andreaniDatosGuia = json_decode(unserialize($shipmentData['andreani_datos_guia']));
                $guiasArray[$shipmentData['increment_id']]     = $andreaniDatosGuia;
            }

        }


        foreach($guiasArray AS $key => $guiaData)
        {
            //$andreaniDatosGuia  = $guiaData->datosguia->GenerarEnviosDeEntregaYRetiroConDatosDeImpresionResult;

            $object             = $guiaData->datosguia->GenerarEnviosDeEntregaYRetiroConDatosDeImpresionResult;

            $helper->crearCodigoDeBarras($object->NumeroAndreani);

        }
        $pdfName    = date_timestamp_get(date_create()).'_'.$orderId;
        $urlHtml    = $helper->getStoreUrl('andreani/generarguia/generarhtml',['order_id' =>$orderId]);
        $file       = $helper->generatePdfFile($pdfName,$urlHtml);

        $pdf = \Zend_Pdf::load($file);
        $fileName = $pdfName.'.pdf';
        $this->_fileFactory->create(
            $fileName,
            str_replace('/Annot /Subtype /Link', '/Annot /Subtype /Link /Border[0 0 0]', $pdf->render()),
            \Magento\Framework\App\Filesystem\DirectoryList::MEDIA,
            'application/pdf'
        );


        unlink($file);
        foreach($guiasArray AS $key => $guiaData)
        {
            //$andreaniDatosGuia  = $guiaData->datosguia->GenerarEnviosDeEntregaYRetiroConDatosDeImpresionResult;

            $object             = $guiaData->datosguia->GenerarEnviosDeEntregaYRetiroConDatosDeImpresionResult;
            unlink($helper->getDirectoryPath('media')."/andreani/".$object->NumeroAndreani.'.png');

        }


    }
}


