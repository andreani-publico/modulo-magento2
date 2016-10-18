<?php
namespace Ids\Andreani\Plugin\Widget;

use Magento\Backend\Block\Widget\Context AS Subject;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Order;


/**
 * Class Context
 * @description Plugin que agrega el botón que genera la guía Andreani en PDF.
 * @author Jhonattan Campo <jcampo@ids.net.ar>
 * @package Ids\Andreani\Plugin\Widget
 */
class Context
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManagerInterface;

    /**
     * @var Order
     */
    protected $_order;


    public function __construct(
        StoreManagerInterface $storeManagerInterface,
        Order $order
    )
    {
        $this->_storeManagerInterface  = $storeManagerInterface;
        $this->_order                  = $order;
    }
    public function afterGetButtonList(
        Subject $subject,
        $buttonList
    )
    {
        //Con el Id de la orden se carga el objeto para obtener el envío.
        $orderId    = $subject->getRequest()->getParam('order_id');
        $order      = $this->_order->load($orderId) ;

        //Recorre la colección de envíos, y verifica si hay datos en el campo asignado
        //para guardar los datos que generarán la guía en PDF.
        $andreaniDatosGuia  = false;

        if($order->getShipmentsCollection())
        {
            $shipmentCollection = $order->getShipmentsCollection();
            foreach($shipmentCollection AS $shipments)
            {
                if($shipments->getAndreaniDatosGuia() !='')
                {
                    $andreaniDatosGuia = true;
                }
            }
        }


        //Valida que el controller esté en la vista de la orden y, que además, haya datos guardados
        //en el campo "andreani_datos_guia".
        $baseUrl = $this->_storeManagerInterface->getStore()->getUrl('andreani/generarguia/index',['order_id' =>$orderId]);

        if($subject->getRequest()->getFullActionName() == 'sales_order_view' && $andreaniDatosGuia){
            $buttonList->add(
                'custom_button',
                [
                    'label'     => __('Imprimir guía Andreani'),
                    'onclick'   => "location.href='{$baseUrl}'",
                    'class'     => 'ship'
                ]
            );
        }

        return $buttonList;
    }
}