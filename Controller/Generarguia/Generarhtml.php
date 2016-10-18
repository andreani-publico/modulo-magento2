<?php

namespace Ids\Andreani\Controller\Generarguia;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\TestFramework\Inspection\Exception;

/**
 * Class Generarhtml
 *
 * @description
 *
 * @author Jhonattan Campo <jcampo@ids.net.ar>
 * @package Ids\Andreani\Controller\Generarguia
 */
class Generarhtml extends Action
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
     * Index constructor.
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct
    (
        Context $context,
        PageFactory $resultPageFactory,
        JsonFactory $resultJsonFactory
    )
    {
        $this->_resultPageFactory   = $resultPageFactory;
        $this->_resultJsonFactory   = $resultJsonFactory;

        parent::__construct($context);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        //Recibe por parámetro el id de la orden, y manda a la librería
        //todos los datos para que genere el html que será posteriormente la guía en PDF.
        //$request = $this->getRequest();
        //$result  = $this->_resultJsonFactory->create();

        //var_dump($request);
        return $this->_resultPageFactory->create();
        //return $result;

        //Habría que generar el bloque para armar el template que crea el pdf de andreani.
/*
        $shipmentId         = $this->getRequest()->getParam('shipment');
        $shipment           = Mage::getModel('sales/order_shipment')->load($shipmentId);

        $andreaniDatosGuia  = $shipment->getAndreaniDatosGuia();
        $andreaniDatosGuia  = json_decode(unserialize($andreaniDatosGuia));

        $block              = $this->getLayout()->createBlock(
            'Mage_Core_Block_Template',
            'generarguia',
            array('template' => 'andreani/guia.phtml')
        )->setData('andreani_datos_guia',$andreaniDatosGuia);

        echo $block->toHtml();*/

//        if($idProvincia = $request->getParam('provincia_id') && $request->isXmlHttpRequest())
        /*     if(true)
             {
                 //traer las localidades de la provincia que vino desde la base de datos
                 $localidades = [
                     ['localidad_id'=>1,'nombre'=>'San justo'],
                     ['localidad_id'=>2,'nombre'=>'Isidro casanova'],
                     ['localidad_id'=>3,'nombre'=>'Ramos mejia']
                 ];

                 if(count($localidades))
                 {
                     return $result->setData(['localidades'=> $localidades]);
                 }
             }

             return $result->setData([]);*/

    }
}


