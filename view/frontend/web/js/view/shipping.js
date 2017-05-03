/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'underscore',
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-address/form-popup-state',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Ui/js/modal/modal',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/checkout-data',
        'uiRegistry',
        'mage/translate',
        'Magento_Checkout/js/model/shipping-rate-service'
    ],
    function (
        $,
        _,
        Component,
        ko,
        customer,
        addressList,
        addressConverter,
        quote,
        createShippingAddress,
        selectShippingAddress,
        shippingRatesValidator,
        formPopUpState,
        shippingService,
        selectShippingMethodAction,
        rateRegistry,
        setShippingInformationAction,
        stepNavigator,
        modal,
        checkoutDataResolver,
        checkoutData,
        registry,
        $t
    ) {
        'use strict';

        var popUp = null;

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/shipping'
            },
            visible: ko.observable(!quote.isVirtual()),
            errorValidationMessage: ko.observable(false),
            isCustomerLoggedIn: customer.isLoggedIn,
            isFormPopUpVisible: formPopUpState.isVisible,
            isFormInline: addressList().length == 0,
            isNewAddressAdded: ko.observable(false),
            saveInAddressBook: 1,
            quoteIsVirtual: quote.isVirtual(),

            /**
             * Array observable que hace que cambien las localidades del select cuando se modifiquen los valores de esta
             */
            localidadesDisponibles: ko.observableArray([]),
            localidadSeleccionada:ko.observable(''),
            provinciasDisponibles: ko.observableArray(
                window.andreaniConfig.provincias
            ),
            provinciaSeleccionada:ko.observable(''),
            sucursalesAndreaniDisponibles: ko.observableArray([]),
            sucursalAndreaniSeleccionada: ko.observable(''),
            nextButtonVisible: ko.observable(false),
            baseUrl: window.andreaniConfig.baseUrl,
            /**
             * @return {exports}
             */
            initialize: function () {
                var self = this,
                    hasNewAddress,
                    fieldsetName = 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset';

                this._super();
                shippingRatesValidator.initFields(fieldsetName);

                if (!quote.isVirtual()) {
                    stepNavigator.registerStep(
                        'shipping',
                        '',
                        $t('Shipping'),
                        this.visible, _.bind(this.navigate, this),
                        10
                    );
                }
                checkoutDataResolver.resolveShippingAddress();

                hasNewAddress = addressList.some(function (address) {
                    return address.getType() == 'new-customer-address';
                });

                this.isNewAddressAdded(hasNewAddress);

                this.isFormPopUpVisible.subscribe(function (value) {
                    if (value) {
                        self.getPopUp().openModal();
                    }
                });

                quote.shippingMethod.subscribe(function () {
                    self.errorValidationMessage(false);
                });

                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var shippingAddressData = checkoutData.getShippingAddressFromData();

                    if (shippingAddressData) {
                        checkoutProvider.set(
                            'shippingAddress',
                            $.extend({}, checkoutProvider.get('shippingAddress'), shippingAddressData)
                        );
                    }
                    checkoutProvider.on('shippingAddress', function (shippingAddressData) {
                        checkoutData.setShippingAddressFromData(shippingAddressData);
                    });
                });

                return this;
            },

            /**
             * Load data from server for shipping step
             */
            navigate: function () {
                //load data from server for shipping step
            },

            /**
             * @return {*}
             */
            getPopUp: function () {
                var self = this,
                    buttons;

                if (!popUp) {
                    buttons = this.popUpForm.options.buttons;
                    this.popUpForm.options.buttons = [
                        {
                            text: buttons.save.text ? buttons.save.text : $t('Save Address'),
                            class: buttons.save.class ? buttons.save.class : 'action primary action-save-address',
                            click: self.saveNewAddress.bind(self)
                        },
                        {
                            text: buttons.cancel.text ? buttons.cancel.text : $t('Cancel'),
                            class: buttons.cancel.class ? buttons.cancel.class : 'action secondary action-hide-popup',
                            click: function () {
                                this.closeModal();
                            }
                        }
                    ];
                    this.popUpForm.options.closed = function () {
                        self.isFormPopUpVisible(false);
                    };
                    popUp = modal(this.popUpForm.options, $(this.popUpForm.element));
                }

                return popUp;
            },

            /**
             * Show address form popup
             */
            showFormPopUp: function () {
                this.isFormPopUpVisible(true);
            },

            /**
             * Save new shipping address
             */
            saveNewAddress: function () {
                var addressData,
                    newShippingAddress;

                this.source.set('params.invalid', false);
                this.source.trigger('shippingAddress.data.validate');

                if (!this.source.get('params.invalid')) {
                    addressData = this.source.get('shippingAddress');
                    // if user clicked the checkbox, its value is true or false. Need to convert.
                    addressData.save_in_address_book = this.saveInAddressBook ? 1 : 0;

                    // New address must be selected as a shipping address
                    newShippingAddress = createShippingAddress(addressData);
                    selectShippingAddress(newShippingAddress);
                    checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
                    checkoutData.setNewCustomerShippingAddress(addressData);
                    this.getPopUp().closeModal();
                    this.isNewAddressAdded(true);
                }
            },

            /**
             * Shipping Method View
             */
            rates: shippingService.getShippingRates(),
            isLoading: shippingService.isLoading,
            isSelected: ko.computed(function () {
                    return quote.shippingMethod() ?
                        quote.shippingMethod().carrier_code + '_' + quote.shippingMethod().method_code
                        : null;
                }
            ),

            /**
             * @description Se mejora la logica de magento para que no se pueda dar al boton "SIGUIENTE" si el usuario no
             *              tiene seleccionada una direccion correctamente, y asi evitar errores al realizar la compra.
             *
             * @param {Object} shippingMethod
             * @return {Boolean}
             */
            selectShippingMethod: function (shippingMethod) {
                selectShippingMethodAction(shippingMethod);
                checkoutData.setSelectedShippingRate(shippingMethod.carrier_code + '_' + shippingMethod.method_code);

                /** AGREGADO **/

                if(shippingMethod.carrier_code == 'andreanisucursal' && shippingMethod.method_code == 'sucursal')
                {
                    $('.retiro-sucursal-row').show();

                    $('#andreanisucursal-provincia').val('');
                    $('#andreanisucursal-localidad').val('').hide();
                    $('#andreanisucursal-sucursal').val('').hide();
                }
                else
                {
                    $('.retiro-sucursal-row').hide();
                }
                /** /AGREGADO **/

                return true;
            },

            /**
             * Set shipping information handler
             */
            setShippingInformation: function () {
                if (this.validateShippingInformation()) {
                    setShippingInformationAction().done(
                        function () {
                            stepNavigator.next();
                        }
                    );
                }
            },

            /**
             * @return {Boolean}
             */
            validateShippingInformation: function () {
                var shippingAddress,
                    addressData,
                    loginFormSelector = 'form[data-role=email-with-possible-login]',
                    emailValidationResult = customer.isLoggedIn();

                if (!quote.shippingMethod()) {
                    this.errorValidationMessage('Please specify a shipping method.');

                    return false;
                }

                if (!customer.isLoggedIn()) {
                    $(loginFormSelector).validation();
                    emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                }

                if (this.isFormInline) {
                    this.source.set('params.invalid', false);
                    this.source.trigger('shippingAddress.data.validate');

                    if (this.source.get('shippingAddress.custom_attributes')) {
                        this.source.trigger('shippingAddress.custom_attributes.data.validate');
                    }

                    if (this.source.get('params.invalid') ||
                        !quote.shippingMethod().method_code ||
                        !quote.shippingMethod().carrier_code ||
                        !emailValidationResult
                    ) {
                        return false;
                    }

                    shippingAddress = quote.shippingAddress();
                    addressData = addressConverter.formAddressDataToQuoteAddress(
                        this.source.get('shippingAddress')
                    );

                    //Copy form data to quote shipping address object
                    for (var field in addressData) {

                        if (addressData.hasOwnProperty(field) &&
                            shippingAddress.hasOwnProperty(field) &&
                            typeof addressData[field] != 'function' &&
                            _.isEqual(shippingAddress[field], addressData[field])
                        ) {
                            shippingAddress[field] = addressData[field];
                        } else if (typeof addressData[field] != 'function' &&
                            !_.isEqual(shippingAddress[field], addressData[field])) {
                            shippingAddress = addressData;
                            break;
                        }
                    }

                    if (customer.isLoggedIn()) {
                        shippingAddress.save_in_address_book = 1;
                    }
                    selectShippingAddress(shippingAddress);
                }

                if (!emailValidationResult) {
                    $(loginFormSelector + ' input[name=username]').focus();

                    return false;
                }

                return true;
            },
            getLocalidades: function ()
            {
                var provinciaSeleccionada = this.provinciaSeleccionada();
                $('.localidad-sin-sucursales').hide();

                if(provinciaSeleccionada)
                {
                    this.localidadesDisponibles([]);
                    this.sucursalesAndreaniDisponibles([]);
                    $('#andreanisucursal-localidad').hide();
                    $('#andreanisucursal-sucursal').hide();
                    $('.retiro-sucursal-row').addClass('andreani-loader');

                    $.ajax(this.baseUrl + 'andreani/localidad/index',
                    {
                        type    : 'post',
                        context : this,
                        data    :
                        {
                            provincia_id: provinciaSeleccionada
                        },
                        success : function (response)
                        {
                            for(var i = 0; i < response.length; i++)
                            {
                                this.localidadesDisponibles.push(response[i]);
                            }
                            $('#andreanisucursal-localidad').show();
                            $('.retiro-sucursal-row').removeClass('andreani-loader');
                        },
                        error   : function (e, status)
                        {
                            alert("Se produjo un error, por favor intentelo nuevamente");
                            $('.retiro-sucursal-row').removeClass('andreani-loader');
                        }
                    });
                }
            },
            getSucursales: function ()
            {
                $('.localidad-sin-sucursales').hide();
                $('#andreanisucursal-sucursal').val('').hide();

                if(this.provinciaSeleccionada() && this.localidadSeleccionada())
                {
                    var provinciaSeleccionada = $('#andreanisucursal-provincia option:selected').text();
                    var localidadSeleccionada = $('#andreanisucursal-localidad option:selected').text();

                    this.sucursalesAndreaniDisponibles([]);
                    $('.retiro-sucursal-row').addClass('andreani-loader');

                    $.ajax(this.baseUrl + 'andreani/sucursal/index',
                    {
                        type    : 'post',
                        context : this,
                        data    :
                        {
                            provincia: provinciaSeleccionada,
                            localidad: localidadSeleccionada
                        },
                        success : function (response)
                        {
                            if(response.length)
                            {
                                $('#andreanisucursal-sucursal').show();

                                for(var i = 0; i < response.length; i++)
                                {
                                    this.sucursalesAndreaniDisponibles.push(response[i]);
                                }
                            }
                            else
                            {
                                $('.localidad-sin-sucursales').show();
                                $('#andreanisucursal-sucursal').hide();
                            }
                            $('.retiro-sucursal-row').removeClass('andreani-loader');
                        },
                        error   : function (e, status)
                        {
                            alert("Se produjo un error, por favor intentelo nuevamente");
                            $('.retiro-sucursal-row').removeClass('andreani-loader');
                        }
                    });
                }
            },
            cotizacionAndreaniSucursal: function ()
            {
                if(this.provinciaSeleccionada() && this.localidadSeleccionada() && this.sucursalAndreaniSeleccionada())
                {
                    $('.retiro-sucursal-row').addClass('andreani-loader');

                    $.ajax(this.baseUrl + 'andreani/webservice/cotizar',
                    {
                        type    : 'post',
                        context : this,
                        data    :
                        {
                            tipo        : 'sucursal',
                            /**
                             * Posiblemente el quote id este de más aca... ver bien
                             */
                            quoteId     : quote.getQuoteId(),
                            sucursalId  : this.sucursalAndreaniSeleccionada(),
                            /**
                             * Temporal: esto se debe cargar directamente cuando se aplica la sucursal en el controller
                             */
                            sucursalTxt : $('#andreanisucursal-sucursal option:selected').text()
                        },
                        success : function (response)
                        {
                            if(typeof response.cotizacion == 'undefined')
                            {
                                $('.retiro-sucursal-row').removeClass('andreani-loader');
                                alert('No se encontraron cotizaciones para el envío a esta sucursal. Por favor intentelo nuevamente seleccionando otra.')
                            }
                            else
                            {
                                //Ver la mejor manera de ponerle el precio
                                $('td.andreanisucursal-price span.price').html(response.cotizacion);
                                $('.retiro-sucursal-row').removeClass('andreani-loader');

                                $.each(this.rates(), function (index, shippingMethod)
                                {
                                    //console.log(shippingMethod.method_code);
                                    if(shippingMethod.method_code == 'sucursal')
                                    {
                                        shippingMethod.method_title = response.method_title;

                                        selectShippingMethodAction(shippingMethod);
                                        checkoutData.setSelectedShippingRate(shippingMethod.carrier_code + '_' + shippingMethod.method_code);
                                    }
                                });
                            }
                        },
                        error   : function (e, status)
                        {
                            alert("Se produjo un error, por favor intentelo nuevamente");
                            $('.retiro-sucursal-row').removeClass('andreani-loader');
                        }
                    });
                }

                return false;
            }
        });
    }
);
