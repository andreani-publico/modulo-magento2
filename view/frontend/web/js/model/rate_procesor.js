define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'magento/storage',
        'Magento_Checkout/js/model/error-processor'
        // additional dependencies
    ],
    function (
        quote,
        shippingService,
        rateRegistry,
        storage,
        errorProcessor
        //,...
    ){
    'use strict';
    return {
        getRates: function(address)
        {
            var cache = rateRegistry.get(address.getKey());

            if (cache)
            {
                shippingService.setShippingRates(cache);
            }
            else
            {
                shippingService.isLoading(true);
                storage.post(
                '%URL for shipping rate estimation%',
                    JSON.stringify({
                       // '%address parameters%'
            }),
                false
            ).done(
                    function (result) {
                        rateRegistry.set(address.getKey(), result);
                        shippingService.setShippingRates(result);
                    }
                ).fail(
                    function (response) {
                        shippingService.setShippingRates([]);
                        errorProcessor.process(response);
                    }
                ).always(
                    function () {
                        shippingService.isLoading(false);
                    }
                );
            }
        }
    };
}
);