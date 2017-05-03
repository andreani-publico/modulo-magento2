/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/address-converter'
    ],
    function(addressList, addressConverter) {
        "use strict";
        return function(addressData) {

            addressData.custom_attributes = {};
            addressData.custom_attributes.altura = {};
            addressData.custom_attributes.altura.attribute_code = 'altura';
            addressData.custom_attributes.altura.value = typeof addressData.altura != "undefined" ? addressData.altura : '';

            addressData.custom_attributes.piso = {};
            addressData.custom_attributes.piso.attribute_code = 'piso';
            addressData.custom_attributes.piso.value = typeof addressData.piso != "undefined" ? addressData.piso : '';

            addressData.custom_attributes.departamento = {};
            addressData.custom_attributes.departamento.attribute_code = 'departamento';
            addressData.custom_attributes.departamento.value = typeof addressData.departamento != "undefined" ? addressData.departamento : '';

            addressData.custom_attributes.celular = {};
            addressData.custom_attributes.celular.attribute_code = 'celular';
            addressData.custom_attributes.celular.value = typeof addressData.celular != "undefined" ? addressData.celular : '';

            addressData.custom_attributes.observaciones = {};
            addressData.custom_attributes.observaciones.attribute_code = 'observaciones';
            addressData.custom_attributes.observaciones.value = typeof addressData.observaciones != "undefined" ? addressData.observaciones : '';

            addressData.custom_attributes.dni = {};
            addressData.custom_attributes.dni.attribute_code = 'dni';
            addressData.custom_attributes.dni.value = typeof addressData.dni != "undefined" ? addressData.dni : '';

            var address = addressConverter.formAddressDataToQuoteAddress(addressData);
            var isAddressUpdated = addressList().some(function(currentAddress, index, addresses) {
                if (currentAddress.getKey() == address.getKey()) {
                    addresses[index] = address;
                    return true;
                }
                return false;
            });
            if (!isAddressUpdated) {
                addressList.push(address);
            } else {
                addressList.valueHasMutated();
            }
            return address;
        };
    }
);