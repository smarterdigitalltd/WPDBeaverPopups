import * as WPDHelpers from './Utils/boxshadow.js'

(function ($) {
    const WPDBBPopupsPageBuilder = {

		/**
		 * Shows the popup style settings lightbox when the popup style settings
		 * is clicked
		 *
		 * @since 1.0
		 * @access private
		 * @method _popupStylesSettingsClicked
		 */
        _popupStylesSettingsClicked () {
            FLBuilder._actionsLightbox.close()
            FLBuilder._showLightbox()
            FLBuilder._closePanel()

            FLBuilder.ajax({
                action: 'wpd_render_bb_popup_styles_settings_form'
            }, WPDBBPopupsPageBuilder._popupStyleSettingsLoaded)
        },

		/**
		 * Sets the lightbox content when the popup style settings have loaded.
		 *
		 * @since 1.0
		 * @access private
		 * @method _popupStyleSettingsLoaded
		 * @param {String} response The JSON with the HTML for the global settings form.
		 */
        _popupStyleSettingsLoaded (response) {
            const data = JSON.parse(response)

            FLBuilder._setSettingsFormContent(data.html)
            WPDBBPopupsPageBuilder._removeFirstTimeUserClassFromPopupOptions()
            WPDBBPopupsPageBuilder._setupSliderFields()
            WPDBBPopupsPageBuilder._setupWidthField()
        },

        /**
         * Remove the class wpd-bb-popup-options-not-clicked to
         * remove the glow effect for first time click
         * @since 1.0.9
         * @private
         * @method _removeFirstTimeUserClassFromPopupOptions
         */
        _removeFirstTimeUserClassFromPopupOptions () {
            if ($('.fl-builder-popup-options-button').hasClass('wpd-bb-popup-options-not-clicked')) {
                $('.fl-builder-popup-options-button').removeClass('wpd-bb-popup-options-not-clicked')
            }
        },

        /**
         * Initiate jQuery slider on certain fields
         * @since 1.0.9
         * @private
         * @method _setupSliderFields
         */
        _setupSliderFields () {
            $('#fl-builder-settings-section-popup_box_shadow input[name="box_shadow_color_opacity"]').attr({
                type: 'range',
                min: '0',
                max: '1',
                step: '0.1'
            })
            if (typeof rangeslider !== 'undefined' && $.isFunction(rangeslider)) {
                $('#fl-builder-settings-section-popup_box_shadow input[name="box_shadow_color_opacity"]').rangeslider()
            }
        },

        /**
         * Add the ability to switch from percentage to px value
         * @since 1.0.9
         * @private
         * @method _setupWidthField
         */
        _setupWidthField () {
            const $pxDesc = $('#fl-field-width').find('.fl-field-description')
            const pxText = $pxDesc.text()
            const newDescription = '<a href="#" class="wpd-px wpd-toggle-value wpd-selected">' + pxText + '</a>' + ' / ' + '<a href="#" class="wpd-full wpd-toggle-value">full</a>'
            const $fakeFullField = $('<input type="hidden" value="Full" class="wpd-fake-field" disabled />')
            const $isFullWidthField = $('<input type="hidden" value="false" name="is_full_width" />')

            $pxDesc
                .html(newDescription)
                .ready(() => {
                    const $links = $pxDesc.find('a')
                    const $input = $($links[0]).closest('div').find('input')

                    $input.after($fakeFullField)
                    $input.after($isFullWidthField)

                    if ('wpd-bb-popup-styles-settings-form' === window.FLBuilderSettingsForms.config.id) {
                        if (window.FLBuilderSettingsForms.config.settings.hasOwnProperty('is_full_width') && window.FLBuilderSettingsForms.config.settings.is_full_width === 'true') {
                            WPDBBPopupsPageBuilder._setWidthFullState({$input, $isFullWidthField, $fakeFullField})
                        }
                    }

                    $pxDesc.find('a').on('click', function() {
                        $links.toggleClass('wpd-selected')

                        if ($(this).hasClass('wpd-full')) {
                            WPDBBPopupsPageBuilder._setWidthFullState({$input, $isFullWidthField, $fakeFullField})
                        }
                        else if ($(this).hasClass('wpd-px')) {
                            WPDBBPopupsPageBuilder._setWidthPxState({$input, $isFullWidthField, $fakeFullField})
                        }

                        $input.trigger('change')
                        $isFullWidthField.trigger('change')
                    })
                })

        },

        /**
         * Set the state of the width field for 'full width' settings
         * @since 1.0.9
         * @private
         * @method _setWidthFullState
         */
        _setWidthFullState (parameters) {
            let { $input, $isFullWidthField, $fakeFullField } = parameters

            $input.closest('div').find('.wpd-toggle-value.wpd-px').removeClass('wpd-selected')
            $input.closest('div').find('.wpd-toggle-value.wpd-full').addClass('wpd-selected')

            $input.attr({
                type: 'hidden'
            })

            $isFullWidthField.attr({
                value: true
            })

            $fakeFullField.attr({
                type: 'text'
            })
        },

        /**
         * Set the state of the width field for 'pixel width' settings
         * @since 1.0.9
         * @private
         * @method _setWidthFullState
         */
        _setWidthPxState (parameters) {
            let { $input, $isFullWidthField, $fakeFullField } = parameters

            $input.removeAttr('max')

            $input.attr({
                type: 'number',
            })

            $fakeFullField.attr({
                type: 'hidden'
            })

            $isFullWidthField.attr({
                value: false
            })
        },

		/**
		 * Saves the global settings when the save button is clicked.
		 *
		 * @since 1.0
		 * @access private
		 * @method _popupStyleSettingsSave
		 */
        _popupStyleSettingsSave () {
            const form = $(this).closest('.fl-builder-settings')
            const valid = form.validate().form()
            const data = form.serializeArray()

            let settings = {}

            if (valid) {
                settings = FLBuilder._getSettings(form)

                FLBuilder.showAjaxLoader()
                FLBuilder._lightbox.close()

                FLBuilder.ajax({
                    action: 'wpd_save_bb_popup_styles_settings',
                    settings: settings
                }, WPDBBPopupsPageBuilder._popupStyleSettingsSaveComplete)
            }
        },

		/**
		 * Saves the popup style settings when the save button is clicked
		 *
		 * @since 1.0
		 * @access private
		 * @method _popupStyleSettingsSaveComplete
		 */
        _popupStyleSettingsSaveComplete () {
            FLBuilder._updateLayout()
        },

		/**
		 * Manually update body class as the body_class filter is only run on
		 * page load, not partial refresh
		 *
		 * @since 1.0
		 * @access private
		 * @method _managePopupStyleBodyClass
		 */
        _managePopupStyleBodyClass () {
            const bodyClasses = document.querySelector('body').classList

            $('body').on('change', '#fl-field-popup_type select[name="popup_type"]', (e) => {
                bodyClasses.forEach((value, index) => {
                    if (value.startsWith(`${WPDPopupConfig.wpdPopupCpt}__`)) {
                        bodyClasses.remove(bodyClasses.item(index))
                    }
                })

                bodyClasses.add(`${WPDPopupConfig.wpdPopupCpt}__${e.target.value}--active`)
            })
        },

		/**
		 * Change the position of the popup when user selects
		 * popup type and position
		 *
		 * @since 1.0.7
		 * @access private
		 * @method _managePopupStyle
		 */
        _managePopupStyle () {
            // const iconWrapper = document.querySelector(`#${WPDPopupConfig.wpdPopupCpt}-${WPDPopupConfig.pageID}__close-button`)
            // const contentWrapper = document.querySelector(`#${WPDPopupConfig.wpdPopupCpt}-${WPDPopupConfig.pageID}__content`)
            const popupWrapper = document.querySelector(`#${WPDPopupConfig.wpdPopupCpt}-${WPDPopupConfig.pageID}__outer`)
            $('body').on('change', '#fl-field-popup_type select[name="popup_type"]', (e) => {
                if (e.target.value === 'fly_out') {
                    // $(iconWrapper).css({
                    //     'position': 'absolute',
                    //     'z-index': '100008',
                    //     'margin-top' : '0'
                    // })
                    // $(contentWrapper).css({
                    //     'position': 'relative'
                    // })
                    $(popupWrapper).css({
                        'position': 'fixed'
                    })
                }
            })

			// Fly Out X position
            $('body').on('change', '#fl-builder-settings-section-fly_out_style select[name="fly_out_x_position"]', (e) => {
                if (e.target.value === 'center') {
                    $(popupWrapper).css({
                        'left': '50%',
                        'right': 'auto',
                        '-webkit-transform': 'translateX(-50%)',
                        '-moz-transform': 'translateX(-50%)',
                        '-ms-transform': 'translateX(-50%)',
                        '-o-transform': 'translateX(-50%)',
                        'transform': 'translateX(-50%)'
                    })
                }
                else if (e.target.value === 'right') {
                    $(popupWrapper).css({
                        'right': '0',
                        'left': 'auto',
                        '-webkit-transform': 'unset',
                        '-moz-transform': 'unset',
                        '-ms-transform': 'unset',
                        '-o-transform': 'unset',
                        'transform': 'unset'
                    })
                }
                else {
                    $(popupWrapper).css({
                        'left': '0',
                        'right': 'auto',
                        '-webkit-transform': 'unset',
                        '-moz-transform': 'unset',
                        '-ms-transform': 'unset',
                        '-o-transform': 'unset',
                        'transform': 'unset'
                    })
                }
            })
			// Fly Out Y position
            $('body').on('change', '#fl-builder-settings-section-fly_out_style select[name="fly_out_y_position"]', (e) => {
                if (e.target.value === 'top') {
                    $(popupWrapper).css({
                        'top': '0',
                        'bottom': 'auto'
                    })
                } else {
                    $(popupWrapper).css({
                        'bottom': '0',
                        'top': 'auto'
                    })
                }
            })
        },

		/**
		 * Manually update body class when the panel is opened/closed
		 *
		 * @since 1.0
		 * @access private
		 * @method _managePanelStateBodyClasss
		 */
        _managePanelStateBodyClass () {
            const bodyClasses = document.querySelector('body').classList

            FLBuilder.addHook('showContentPanel', () => bodyClasses.add('fl-builder-panel--open'))
            FLBuilder.addHook('hideContentPanel', () => bodyClasses.remove('fl-builder-panel--open'))
        },

		/**
		 * Manually update icon styles as they're outside of a module (and
		 * aren't therefore updated by partial refresh)
		 *
		 * @since 1.0
		 * @access private
		 * @method _manageIconStyles
		 */
        _manageIconStyles () {
            const iconWrapper = document.querySelector(`#${WPDPopupConfig.wpdPopupCpt}-${WPDPopupConfig.pageID}__close-button`)
            const icon = document.querySelector(`#${WPDPopupConfig.wpdPopupCpt}-${WPDPopupConfig.pageID}__close-button svg`)
            const iconPath = document.querySelector(`#${WPDPopupConfig.wpdPopupCpt}-${WPDPopupConfig.pageID}__close-button path`)

			// Special treatment for font size
            $('body').on('change', '#fl-builder-settings-section-close_icon_style input[name="close_icon_size"]', (e) => {
                $(iconWrapper).css({ 'width': e.target.value + 'px', 'height': e.target.value + 'px' })
            })

			// Special treatment for icon colour
            $('body').on('change', '#fl-builder-settings-section-close_icon_style .fl-color-picker', (e) => {
                $(iconPath).css({ 'fill': '#' + e.target.value })
            })

			// Special treatment for icon
            $('body').on('change', '#fl-builder-settings-section-close_icon_style .fl-icon-field', (e) => {
                $(icon).removeClass().addClass(e.target.value)
            })

			// Hide/show close icon
            $('body').on('change', '#fl-builder-settings-section-modal_style #fl-field-modal_disable_close_icon select', (e) => {
                e.target.value === 'yes' ? $(icon).css('display', 'none') : $(icon).css('display', 'block')
            })

			// Top Right Position
            $('body').on('change', '#fl-builder-settings-section-close_icon_style input[name="close_icon_vertical_distance"]', (e) => {
                $(iconWrapper).css({ 'top': e.target.value + 'px' })
            })

			// Right Position
            $('body').on('change', '#fl-builder-settings-section-close_icon_style input[name="close_icon_horizontal_distance"]', (e) => {
                $(iconWrapper).css({ 'right': e.target.value + 'px' })
            })

			// Icon Position
            // $('body').on('change', '#fl-builder-settings-section-close_icon_style select[name="modal_close_icon_position"]', (e) => {
             //    if (e.target.value === 'overlay') {
			// 		 $(iconWrapper).css({
			// 			 'position': 'fixed',
			// 			 'margin-top' : '43px',
			// 			 'z-index': 'auto'
			// 		 })
             //   	}
             //   	else {
			// 		 $(iconWrapper).css({
			// 			 'position': 'absolute',
			// 			 'z-index': '100008',
			// 			 'margin-top' : '0'
			// 		 })
             //    }
            // })
        },

		/**
		 * Manually update background styles with JS
		 *
		 * @since 1.0
		 * @access private
		 * @method _manageBackgroundStyles
		 */
        _manageBackgroundStyles () {
            const popupWrapper = document.querySelector(`#${WPDPopupConfig.wpdPopupCpt}-${WPDPopupConfig.pageID}__outer`)

            $('body').on('change', 'select[name="modal_overlay_background_type"]', (e) => {
                if (e.currentTarget.value === 'color') {
                    $(popupWrapper).css({
                        'background-image': 'inherit',
                        'background-color': $('input[name="modal_overlay_background_color"]').val().includes('rgb') ? $('input[name="modal_overlay_background_color"]').val() : '#' + $('input[name="modal_overlay_background_color"]').val()
                    })
                }
                else if (e.currentTarget.value === 'image') {
                    $(popupWrapper).css({
                        'background-image': 'url(' + $('select[name="modal_overlay_background_image_src"]').val() + ')',
                        'background-color': 'inherit'
                    })
                }
            })

            $('body').on('change', 'input[name="modal_overlay_background_color"]', (e) => {
                $(popupWrapper).css({
                    'background-image': 'inherit',
                    'background-color': e.currentTarget.value.includes('rgb') ? e.currentTarget.value : '#' + e.currentTarget.value
                })
            })

            $('body').on('change', 'select[name="modal_overlay_background_image_src"]', (e) => {
                $(popupWrapper).css({
                    'background-color': 'inherit',
                    'background-image': 'url(' + e.currentTarget.value + ')'
                })
            })

			/**
			 * Popup Overlay Image Repeat
			 * Popup Overlay Image Size
			 * Popup Overlay Image Position
			 */
			// Popup Overlay Image Repeat
            $('body').on('change', 'select[name="modal_overlay_background_image_repeat"]', (e) => {
                $(popupWrapper).css({
                    'background-repeat': e.currentTarget.value
                })
            })
			// Popup Overlay Image Size
            $('body').on('change', 'select[name="modal_overlay_background_image_size"]', (e) => {
                $(popupWrapper).css({
                    'background-size': e.currentTarget.value
                })
            })

			/**
			 * Popup Overlay Image Position
			 * @internal There is only one option for Popup Overlay Image Position therefore, the CSS value is hardcoded here.
			 */
            $('body').on('change', 'select[name="modal_overlay_background_image_position"]', (e) => {
                $(popupWrapper).css({
                    'background-position': 'center'
                })
            })
        },

		/**
		 * Update popup structure
		 *
		 * @since 1.0
		 * @access private
		 * @method _managePopupStructureStyles
		 */
        _managePopupStructureStyles () {
			/**
			 * Popup structure - width
			 * Popup structure - height
			 * Popup structure - border radius
			 */
            const popupWrapper = document.querySelector(`#${WPDPopupConfig.wpdPopupCpt}-${WPDPopupConfig.pageID}__content .fl-builder-content`)

			// Popup structure - width
            $('body').on('change', '#fl-builder-settings-section-popup_structure input[name="width"]', (e) => {
                $(popupWrapper).css({ 'width': e.target.value + 'px' })
            })

			// Popup structure - width
            $('body').on('change', '#fl-builder-settings-section-popup_structure input[name="is_full_width"]', (e) => {
                if ('true' === e.target.value) {
                    $(popupWrapper).css({ 'width': '100vw' })
                }
            })

			// Popup structure - height
            $('body').on('change', '#fl-builder-settings-section-popup_structure input[name="height"]', (e) => {
                const value = e.target.value.length ? e.target.value + 'px' : 'auto'
                $(popupWrapper).css({ 'height': value })
            })

			// Popup structure - border radius
            $('body').on('change', '#fl-builder-settings-section-popup_structure input[name="border_radius"]', (e) => {
                $(popupWrapper).css({ 'border-radius': e.target.value + 'px' })
            })
        },

		/**
		 * Update popup structure
		 *
		 * @since 1.0.0
		 * @access private
		 * @method _managePopupBox
		 */
        _managePopupBoxShadow () {
			// Box shadow
            const popupShadowWrapper = document.querySelector(`#${WPDPopupConfig.wpdPopupCpt}-${WPDPopupConfig.pageID}__content .fl-builder-content`)

            $('body').on('change', '#fl-builder-settings-section-popup_box_shadow select[name="add_box_shadow"]', (e) => {
                let boxShadowCss = e.target.value === 'yes' ? WPDBBPopupsPageBuilder._calculatePopupBoxShadow() : 'none'

                $(popupShadowWrapper).css('box-shadow', boxShadowCss)
            })

            $('body').on('keyup change', '#fl-builder-settings-section-popup_box_shadow input', (e) => {
                let boxShadowCss = WPDBBPopupsPageBuilder._calculatePopupBoxShadow()

                $(popupShadowWrapper).css('box-shadow', boxShadowCss)
            })
        },

        /**
         * Get box shadow
         * @since 1.0.0
         * @return {string}
         * @private
         */
        _calculatePopupBoxShadow () {
            let boxShadowParams = {
                horizontal: parseInt($('#fl-builder-settings-section-popup_box_shadow input[name="box_shadow_horizontal_length"]').val()) ? $('#fl-builder-settings-section-popup_box_shadow input[name="box_shadow_horizontal_length"]').val() : 0,
                vertical: parseInt($('#fl-builder-settings-section-popup_box_shadow input[name="box_shadow_vertical_length"]').val()) ? $('#fl-builder-settings-section-popup_box_shadow input[name="box_shadow_vertical_length"]').val() : 0,
                spread: parseInt($('#fl-builder-settings-section-popup_box_shadow input[name="box_shadow_spread_radius"]').val()) ? $('#fl-builder-settings-section-popup_box_shadow input[name="box_shadow_spread_radius"]').val() : 0,
                blur: parseInt($('#fl-builder-settings-section-popup_box_shadow input[name="box_shadow_blur_radius"]').val()) ? $('#fl-builder-settings-section-popup_box_shadow input[name="box_shadow_blur_radius"]').val() : 0,
                color: $('#fl-builder-settings-section-popup_box_shadow input[name="box_shadow_color"]').val() ? $('#fl-builder-settings-section-popup_box_shadow input[name="box_shadow_color"]').val() : '000',
                opacity: $('#fl-builder-settings-section-popup_box_shadow input[name="box_shadow_color_opacity"]').val() ? $('#fl-builder-settings-section-popup_box_shadow input[name="box_shadow_color_opacity"]').val() : 0.5
            }

            return WPDHelpers.createBoxShadow(boxShadowParams)
        },

		/**
		 * Initializes the WPD popup options
		 *
		 * @since 1.0
		 * @access private
		 * @method init
		 */
        init () {
            WPDBBPopupsPageBuilder._bindEvents()
            WPDBBPopupsPageBuilder._manageIconStyles()
            WPDBBPopupsPageBuilder._managePopupStyleBodyClass()
            WPDBBPopupsPageBuilder._managePanelStateBodyClass()
            WPDBBPopupsPageBuilder._manageBackgroundStyles()
            WPDBBPopupsPageBuilder._managePopupStyle()
            WPDBBPopupsPageBuilder._managePopupStructureStyles()
            WPDBBPopupsPageBuilder._managePopupBoxShadow()
        },

		/**
		 * Binds most of the events for the interface.
		 *
		 * @since 1.0
		 * @access private
		 * @method _bindEvents
		 */
        _bindEvents () {
            $('body').on('click', '.fl-builder-popup-options-button', WPDBBPopupsPageBuilder._popupStylesSettingsClicked)
            $('body').on('click', '.wpd-bb-popup-styles-settings-form .fl-builder-settings-save', WPDBBPopupsPageBuilder._popupStyleSettingsSave)
        }
    }

    WPDBBPopupsPageBuilder.init()
})(jQuery)
