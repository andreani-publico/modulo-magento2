/*global define*/
define([
    'Magento_Ui/js/form/form'
], function(Component) {
    'use strict';
    return Component.extend({
        initialize: function () {
            this._super();
            // component initialization logic
            return this;
        },

        /**
         * Form submit handler
         *
         * This method can have any name.
         */
        onSubmit: function()
        {
            this.source.set('params.invalid', false);
            this.source.trigger('customCheckoutForm.data.validate');

            if (!this.source.get('params.invalid'))
            {
                var formData = this.source.get('customCheckoutForm');
                // do something with form data
                console.dir(formData);
            }
        }
    });
});