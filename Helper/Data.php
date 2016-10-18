<?php
/**
 * Class Data
 *
 * @description Helper base para el módulo Andreani
 * @author Jhonattan Campo <jcampo@ids.net.ar>
 * @package Ids\Andreani\Helper
 *
 */

namespace Ids\Andreani\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Sales\Model\Order;


class Data extends AbstractHelper
{
    /**
     * Constantes para usar con los métodos del WS.
     */
    const ENVMODPROD 						= 'prod';
    const ENVMODTEST 						= 'dev';
    const COTIZACION 						= 'cotizacion';
    const TRAZABILIDAD 						= 'trazabilidad';
    const IMPRESIONCONSTANCIA 				= 'impresionconstancia';
    const OBTESTADODIST 					= 'obtenerestadodistribucion';
    const SUCURSALES 						= 'sucursales';
    const CONFIRCOMPRA 						= 'confirmacioncompra';
    const GENENVIOENTREGARETIROIMPRESION 	= 'generarenviosdeentregayretirocondatosdeimpresion';
    const ANULARENVIO 	                    = 'anularenvio';
    const MEDIDA_GRAMOS                     = 'gramos';
    const MEDIDA_KILOS                      = 'kilos';
    const COTIZACION_TABLA                  = 'tabla';
    const COTIZACION_ONLINE                 = 'webservice';

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;
    

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var Cart
     */
    protected $_cart;

    /**
     * @var AddressFactory
     */
    protected $_addressFactory;

    /**
     * @var AddressFactory
     */
    protected $_productRepository;

    /**
     * @var AddressFactory
     */
    protected $_directoryList;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManagerInterface;


    /**
     * @var Shipment
     */
    protected $_order;


    /**
     * @var ProductLoader
     */
    protected $_productloader;
    /**
     * Data constructor.
     * @param Session $checkoutSession
     * @param Cart $cart
     * @param ScopeConfigInterface $scopeConfig
     * @param DirectoryList $directoryList
     * @param StoreManagerInterface $storeManagerInterface
     */
    public function __construct(
        Session $checkoutSession,
        Cart $cart,
        ScopeConfigInterface $scopeConfig,
        DirectoryList $directoryList,
        StoreManagerInterface $storeManagerInterface,
        ProductFactory $productFactory,
        Order $order
    ) {
        $this->_checkoutSession         = $checkoutSession;
        $this->_cart                    = $cart;
        $this->_scopeConfig             = $scopeConfig;
        $this->_directoryList           = $directoryList;
        $this->_storeManagerInterface   = $storeManagerInterface;
        $this->_productloader           = $productFactory;
        $this->_order                   = $order;
        //parent::__construct($context);
    }

    /**
     * @description devuelve el usuario, que se configuró por admin.
     * @return mixed
     */
    public function getUsuario()
    {
        return $this->_scopeConfig->getValue('shipping/andreani_configuracion/usuario');
    }
    /**
     * @description devuelve el número de cliente que se configuró por admin.
     * @return mixed
     */
    public function getNroCliente()
    {
        return $this->_scopeConfig->getValue('shipping/andreani_configuracion/numero_cliente');
    }

    /**
     * @description devuelve la contraseña asignada al usuario que se configuró por admin.
     * @return mixed
     */
    public function getPass()
    {
        return $this->_scopeConfig->getValue('shipping/andreani_configuracion/password');
    }

    /**
     * @description devuelve el método de envío a sucursal, que se configuró por admin.
     * @return mixed
     */
    public function getMetodo()
    {
        return $this->_scopeConfig->getValue('shipping/andreani_configuracion/metodo');
    }

    /**
     * @description devuelve la unidad de medida, que se configuró por admin.
     * @return mixed
     */
    public function getModo()
    {
        return $this->_scopeConfig->getValue('shipping/andreani_configuracion/modo');
    }

    /**
     * @description determina si está activa o no la caché de sucursales optimizada que se configuró por admin.
     * @return mixed
     */
    public function getCache()
    {
        return $this->_scopeConfig->getValue('shipping/andreani_configuracion/cache');
    }

    /**
     * @description devuelve el path del logo de la empresa que se cargó por admin.
     * @return string
     */
    public function getlogoEmpresaPath()
    {
        $storeUrl           = $this->_storeManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $logoEmpresa        = $this->_scopeConfig->getValue('shipping/andreani_configuracion/upload_image');
        $logoEmpresaPath    = $storeUrl.'andreani/logo_empresa/'.$logoEmpresa;
        return $logoEmpresaPath ;
    }

    public function getCodigoBarras($numeroAndreani)
    {
        return $this->_storeManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)."/andreani/{$numeroAndreani}.png";
        
    }
    /**
     * @description apartir de la interface devuele la dirección de la url de la tienda.
     * @param $type
     * @return mixed
     */
    public function getStoreUrl($path,$params)
    {
        return $this->_storeManagerInterface->getStore()->getUrl($path,$params);
//        return $this->_storeManagerInterface->getStore()->getBaseUrl();
    }

    public function getStoreManagerInterface()
    {
        return $this->_storeManagerInterface->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @description devuelve la unidad de medida, que se configuró por admin.
     * @return mixed
     */
    public function getUnidadMedida()
    {
        return $this->_scopeConfig->getValue('shipping/andreani_configuracion/unidad_medida');
    }

    /**
     * @description devuelve el peso máximo habilitado, que se configuró por admin.
     * @return mixed
     */
    public function getPesoMaximo()
    {
        return $this->_scopeConfig->getValue('shipping/andreani_configuracion/peso_maximo');
    }

    /**
     * @description devuelve el tipo de cotizacion: por webservice o por tarifario con rangos de peso seteados en el admin.
     * @return mixed
     */
    public function getTipoCotizacion()
    {
        return $this->_scopeConfig->getValue('shipping/andreani_configuracion/tipo_cotizacion');
    }

    /**
     * @description devuelve el número de contrato del método "Andreani Sucursal"
     * que se configuró por admin.
     * @return mixed
     */
    public function getSucursalContrato()
    {
        return $this->_scopeConfig->getValue('carriers/andreanisucursal/contrato');
    }

    /**
     * @description devuelve el número de contrato del método "Andreani Urgente"
     * que se configuró por admin.
     * @return mixed
     */
    public function getUrgenteContrato()
    {
        return $this->_scopeConfig->getValue('carriers/andreaniurgente/contrato');
    }

    /**
     * @description devuelve el número de contrato del método "Andreani Estandar"
     * que se configuró por admin.
     * @return mixed
     */
    public function getEstandarContrato()
    {
        return $this->_scopeConfig->getValue('carriers/andreaniestandar/contrato');
    }

    /**
     * @description devuelve el objeto del "quote" que está en sesión.
     * @return Quote
     */
    public function getQuote()
    {
        return $this->_cart->getQuote();
    }

    /**
     * @description devuelve el objeto de la dirección del cliente, apartir de
     * la dirección de envío por defecto.
     * @return mixed
     */
    public function getDefaultShippingData()
    {
        $defaultShippingId = $this->getQuote()->getCustomer()->getDefaultShipping();
        $address = $this->_addressFactory->create()->load($defaultShippingId);
        return $address->getData();
    }

    /**
     * @param $mensaje String
     * @param $archivo String
     */
    public static function log($mensaje,$archivo)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/'.$archivo);
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($mensaje);
    }

   
    /**
     * @description Método que espera un parámetro con el método que definirá la url a traer para el
     * WS; además, es posible pasarle el ambiente (testing o prod) para que traiga la url correspondiente.
     * @param $method
     * @param null $enviroment
     * @return mixed
     */
    public function getWSMethodUrl($method,$enviroment=null)
    {

        if($enviroment == self::ENVMODPROD)
        {
            $configField = 'shipping/andreani_configuracion/andreani_ws_prod_urls/';
        }
        else
        {
            $configField = 'shipping/andreani_configuracion/andreani_ws_dev_urls/';
        }

        switch($method)
        {
            case self::COTIZACION:

                $url = $this->_scopeConfig->getValue($configField.$method);
                break;
            case self::TRAZABILIDAD:
                $url = $this->_scopeConfig->getValue($configField.$method);
                break;
            case self::IMPRESIONCONSTANCIA:
                $url = $this->_scopeConfig->getValue($configField.$method);
                break;
            case self::OBTESTADODIST:
                $url = $this->_scopeConfig->getValue($configField.$method);
                break;
            case self::SUCURSALES:
                $url = $this->_scopeConfig->getValue($configField.$method);
                break;
            case self::CONFIRCOMPRA:
                $url = $this->_scopeConfig->getValue($configField.$method);
                break;
            case self::GENENVIOENTREGARETIROIMPRESION:
                $url = $this->_scopeConfig->getValue($configField.$method);
                break;
            default:
                $url = '';
                break;
        }

        return $url;

    }

    /**
     * @description recibe el parámetro del directorio que se desea acceder, si no se pasa el parámetro,
     * por defecto devuelve la ruta de la raíz del proyecto.
     * -----------------------------------------------------
     * Paths:
     * -> app
     * -> etc
     * -> lib_internal
     * -> lib_web
     * -> pub
     * -> static
     * -> var
     * -> tmp
     * -> cache
     * -> log
     * -> session
     * -> setup
     * -> di
     * -> generation
     * -> upload
     * -> composer_home
     * -> view_preprocessed
     * -> html
     * -----------------------------------------------------
     * @return mixed
     */
    public function getDirectoryPath($path=null)
    {
        if($path)
        {
            return $this->_directoryList->getPath($path);
        }
        else
        {
            return $this->_directoryList->getRoot();
        }
        
    }

    /**
     * @description recibe el número de andreani y crea el código de barras a partir del texto.
     * @param $text
     */
    public function crearCodigoDeBarras($text) {
        $text = strtoupper($text);
        $code_string = "";
        $chksum = 103;
        $code_array = array(" " => "212222", "!" => "222122", "\"" => "222221", "#" => "121223", "$" => "121322", "%" => "131222", "&" => "122213", "'" => "122312", "(" => "132212", ")" => "221213", "*" => "221312", "+" => "231212", "," => "112232", "-" => "122132", "." => "122231", "/" => "113222", "0" => "123122", "1" => "123221", "2" => "223211", "3" => "221132", "4" => "221231", "5" => "213212", "6" => "223112", "7" => "312131", "8" => "311222", "9" => "321122", ":" => "321221", ";" => "312212", "<" => "322112", "=" => "322211", ">" => "212123", "?" => "212321", "@" => "232121", "A" => "111323", "B" => "131123", "C" => "131321", "D" => "112313", "E" => "132113", "F" => "132311", "G" => "211313", "H" => "231113", "I" => "231311", "J" => "112133", "K" => "112331", "L" => "132131", "M" => "113123", "N" => "113321", "O" => "133121", "P" => "313121", "Q" => "211331", "R" => "231131", "S" => "213113", "T" => "213311", "U" => "213131", "V" => "311123", "W" => "311321", "X" => "331121", "Y" => "312113", "Z" => "312311", "[" => "332111", "\\" => "314111", "]" => "221411", "^" => "431111", "_" => "111224", "NUL" => "111422", "SOH" => "121124", "STX" => "121421", "ETX" => "141122", "EOT" => "141221", "ENQ" => "112214", "ACK" => "112412", "BEL" => "122114", "BS" => "122411", "HT" => "142112", "LF" => "142211", "VT" => "241211", "FF" => "221114", "CR" => "413111", "SO" => "241112", "SI" => "134111", "DLE" => "111242", "DC1" => "121142", "DC2" => "121241", "DC3" => "114212", "DC4" => "124112", "NAK" => "124211", "SYN" => "411212", "ETB" => "421112", "CAN" => "421211", "EM" => "212141", "SUB" => "214121", "ESC" => "412121", "FS" => "111143", "GS" => "111341", "RS" => "131141", "US" => "114113", "FNC 3" => "114311", "FNC 2" => "411113", "SHIFT" => "411311", "CODE C" => "113141", "CODE B" => "114131", "FNC 4" => "311141", "FNC 1" => "411131", "Start A" => "211412", "Start B" => "211214", "Start C" => "211232", "Stop" => "2331112");
        $code_keys = array_keys($code_array);
        $code_values = array_flip($code_keys);
        for ($x = 1; $x <= strlen($text); $x++) {
            $activeKey = substr($text, ($x - 1), 1);
            $code_string .= $code_array[$activeKey];
            $chksum = ($chksum + ($code_values[$activeKey] * $x));
        }
        $code_string .= $code_array[$code_keys[($chksum - (intval($chksum / 103) * 103))]];
        $code_string = "211412" . $code_string . "2331112";
        $code_length = 20;
        for ($i = 1; $i <= strlen($code_string); $i++) {
            $code_length = $code_length + (integer) (substr($code_string, ($i - 1), 1));
        }
        $img_width = $code_length;
        $img_height = 100;
        $image = imagecreate($img_width, $img_height);
        $black = imagecolorallocate($image, 0, 0, 0);
        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        $location = 10;
        for ($position = 1; $position <= strlen($code_string); $position++) {
            $cur_size = $location + ( substr($code_string, ($position - 1), 1) );
            imagefilledrectangle($image, $location, 0, $cur_size, $img_height, ($position % 2 == 0 ? $white : $black));
            $location = $cur_size;
        }

        $filePath 		= $this->getDirectoryPath('media')."/andreani/";
        if (!file_exists($filePath) || !is_dir($filePath)) {
            mkdir("{$filePath}", 0777,true);
        }

        $filename = $filePath."{$text}.png";
        imagepng($image, $filename);
        imagesavealpha($image, true);
        imagedestroy($image);
    }

    /**
     * @description método encargado de generar la guía de Andreani pasando los parámetros que
     * necesita la librería para armar el PDF.
     * @param $pdfName
     * @param $urlHtml
     * @return string
     */
    public function generatePdfFile($pdfName,$urlHtml)
    {
        $pathWkhtmlToX 	= $this->getDirectoryPath().'/vendor/bin/wkhtmltopdf-i386';
        $filePath 		= $this->getDirectoryPath('media')."/andreani/";
        if (!file_exists($filePath) || !is_dir($filePath)) {
            mkdir("{$filePath}", 0777,true);
        }
        $file 			= $filePath."{$pdfName}.pdf";
        exec("{$pathWkhtmlToX} {$urlHtml} {$file}");
        return $file;
    }

    /**
     * @description devuelve la versión del soap correspondiente al método que se desea consultar al WS.
     * @param $method
     * @param null $enviroment
     * @return mixed
     */
    public function getSoapVersion($method,$enviroment=null)
    {
        if($enviroment == self::ENVMODPROD)
        {
            $configField = 'shipping/andreani_configuracion/andreani_ws_prod_urls/';
        }
        else
        {
            $configField = 'shipping/andreani_configuracion/andreani_ws_dev_urls/';
        }

        switch($method)
        {
            case self::COTIZACION:
                $soapVersion = $this->_scopeConfig->getValue($configField.$method.'_soap_version');
                break;
            case self::TRAZABILIDAD:
                $soapVersion = $this->_scopeConfig->getValue($configField.$method.'_soap_version');
                break;
            case self::IMPRESIONCONSTANCIA:
                $soapVersion = $this->_scopeConfig->getValue($configField.$method.'_soap_version');
                break;
            case self::OBTESTADODIST:
                $soapVersion = $this->_scopeConfig->getValue($configField.$method.'_soap_version');
                break;
            case self::SUCURSALES:
                $soapVersion = $this->_scopeConfig->getValue($configField.$method.'_soap_version');
                break;
            case self::CONFIRCOMPRA:
                $soapVersion = $this->_scopeConfig->getValue($configField.$method.'_soap_version');
                break;
            case self::GENENVIOENTREGARETIROIMPRESION:
                $soapVersion = $this->_scopeConfig->getValue($configField.$method.'_soap_version');
                break;
            case self::ANULARENVIO:
                $soapVersion = $this->_scopeConfig->getValue($configField.$method.'_soap_version');
                break;
            default:
                $soapVersion = '';
                break;
        }

        return $soapVersion;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }

    /**
     * @description devuelve orden por ID.
     * @param $orderId
     * @return $this
     */
    public function getLoadShipmentOrder($orderId)
    {
        return $this->_order->load($orderId);
    }

    /**
     * @description Devuelve la url para qeu el cliente pueda ver el estado de su envio en andreani
     *
     * @param $trackingNumber
     * @return string
     */
    public function getTrackingUrl($trackingNumber)
    {
        return $this->_scopeConfig->getValue('shipping/andreani_configuracion/tracking_url').DIRECTORY_SEPARATOR.$trackingNumber;
    }
}
