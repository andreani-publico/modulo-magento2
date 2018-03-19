/**
 *
 * @name 'shippingcalc.js'
 *
 * @description Calculate shipping rate
 *
 */
define([
    'jquery',
    'jquery/ui',
    'mage/translate'
], function ($) {
    "use strict";

    /**
     *
     * Calculate shipping rate in an ajax call
     *
     * @param {object} config  Config object. Has an 'idPostalCodeContainer' property to get the container ID to get the postal code, and it has an 'idRateContainer' property too, to get the container ID to put the shipping rate calculated
     * @param {object} element Object button that when it is clicked, it dispatch the AJAX call to calculate the shipping rate
     *
     */
    function main(config, element) {
        $(element).on('click', function() {
            var postcode = $(config['idPostalCodeContainer']).val();

            var productid = $(config['idProductContainer']).val();

            var productqty = $('#qty').val();

            /**
             *
             * @type {string} Set is an ajax call
             *
             */
            var isAjax = 1;

            /**
             *
             * @type {{ajax: string, postcode: (*)}} Request params to calculate shipping rate
             *
             */
            var params = {
                ajax    : isAjax,
                postcode: postcode,
                productid: productid,
                productqty: productqty
            };

            $.ajax({
                showLoader: true,
                url       : '/andreani/index/shippingcalc',
                data      : params,
                type      : 'POST',
                dataType  : 'json'
            }).done(function (data) {
                $(config['idRateContainer']).html(data['response']);
            });
        });
    }

    return main;
});