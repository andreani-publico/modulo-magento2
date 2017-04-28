/**
 * @description Sobreescritura del js y template html que manejan la logica y renderizacion de
 *              los metodos de envio en el checkout.
 *
 *
 * @file Magento_Checkout/js/view/shipping-address/address-renderer/default Se sobreescribe para que al seleccionar una direccion de
 * envio, los radiobutton de seleccion se reseteen.
 *
 * @file Magento_Checkout/js/view/shipping Se sobreescribe para añadir la logica de seleccion de sucursales andreani, y
 * mejorar el comportamiento por defecto de magento.
 *
 * @file Magento_Checkout/template/shipping.html Se sobreescribe para que al seleccionar una direccion de
 * envio, los radiobutton de seleccion se reseteen.
 *
 * @type {{map: {*: {}}}}
 */
var config = {
    map: {
        '*': {
            'Magento_Checkout/js/view/shipping-address/address-renderer/default':
                'Ids_Andreani/js/view/shipping-address/address-renderer/default',
            'Magento_Checkout/js/view/shipping':
                'Ids_Andreani/js/view/shipping',
            'Magento_Checkout/template/shipping':
                'Ids_Andreani/template/shipping',
            'Magento_Checkout/template/shipping-address/form':
                'Ids_Andreani/template/shipping-address/form',
            'Magento_Checkout/template/billing-address':
                'Ids_Andreani/template/billing-address',
            'Magento_Checkout/template/billing-address/details':
                'Ids_Andreani/template/billing-address/details',
            'Magento_Checkout/template/billing-address/form':
                'Ids_Andreani/template/billing-address/form',
            'Magento_Checkout/template/billing-address/list':
                'Ids_Andreani/template/billing-address/list',
            'Magento_Checkout/js/model/shipping-save-processor/default':
                'Ids_Andreani/js/model/shipping-save-processor/default',
            'Magento_Checkout/template/cart/shipping-rates':
                'Ids_Andreani/template/cart/shipping-rates',
            'Magento_Checkout/js/view/cart/shipping-rates':
                'Ids_Andreani/js/view/cart/shipping-rates',
            'Magento_Checkout/js/view/billing-address':
                'Ids_Andreani/js/view/billing-address',
            'Magento_Checkout/template/shipping-address/address-renderer/default':
                'Ids_Andreani/template/shipping-address/address-renderer/default',
            'Magento_Checkout/template/shipping-information/address-renderer/default':
                'Ids_Andreani/template/shipping-information/address-renderer/default',
            'Magento_Checkout/js/view/shipping-information/address-renderer/default':
                'Ids_Andreani/js/view/shipping-information/address-renderer/default',
            'Magento_Checkout/js/action/create-shipping-address':
                'Ids_Andreani/js/action/create-shipping-address',
            'Magento_Checkout/js/model/checkout-data-resolver':
                'Ids_Andreani/js/model/checkout-data-resolver'

        }
    }
};