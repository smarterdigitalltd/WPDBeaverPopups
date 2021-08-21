(function ($) {
    WPDBBPopupsUABBIntegration = {

        _manageValidation (e) {
            let linkField = document.querySelector('.fl-builder-uabb-button-settings #fl-field-link .fl-link-field-input')

            if (e.target.value === 'popup' || e.target.value === 'close_popup') {
                linkField.classList.add('fl-ignore-validation')
            }
            else {
                linkField.classList.remove('fl-ignore-validation')
            }
        },

        /**
         * Initializes the WPD popup options
         *
         * @since 1.0
         * @access private
         * @method _init
         */
        _init () {
            WPDBBPopupsUABBIntegration._bindEvents()
        },

        /**
         * Binds most of the events for the interface.
         *
         * @since 1.0
         * @access private
         * @method _bindEvents
         */
        _bindEvents () {
            $('body').delegate('.fl-builder-uabb-button-settings #fl-field-click_action select', 'change', WPDBBPopupsUABBIntegration._manageValidation)
        }
    }

    WPDBBPopupsUABBIntegration._init()
})(jQuery)
