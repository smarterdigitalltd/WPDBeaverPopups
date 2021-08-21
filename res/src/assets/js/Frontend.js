import Jbox from 'jbox'
import cloneDeep from 'lodash/cloneDeep'
import debounce from 'lodash/debounce'
import Cookie from 'js-cookie'
import deviceDetector from './Utils/deviceDetector'

require('waypoints/lib/jquery.waypoints')

const WPDBBPopupsFrontEnd = {
	/**
	 * An object of queued popups that will run on this page
	 *  {
	 *      trigger => [
	 *          id,
	 *          Jbox,
	 *          script,
	 *          settings
	 *      ]
	 *  }
	 *
	 * @since 1.0.0
	 * @access private
	 * @property {Object} _willRunTriggers
	 */
	_willRunTriggers: {},

	/**
	 * An array of popups that have run on this page
	 *  [ trigger ]
	 *
	 * @since 1.0.0
	 * @access private
	 * @property {Array} _hasRunTriggers
	 */
	_hasRunTriggers: [],

	/**
	 * Array of popup enabled button modules
	 *
	 * @since 1.0.0
	 * @access private
	 * @property {Array} _popupEnabledButtonModules
	 */
	_popupEnabledButtonModules: [],

	/**
	 * Array of close popup enabled button modules
	 *
	 * @since 1.0.6
	 * @access private
	 * @property {Array} _closeButtonModules
	 */
	_closeButtonModules: [],

	/**
	 * Array of popup enabled Links
	 *
	 * @since 1.1.1
	 * @private
	 * @property {Array} _popupEnabledLinkItems
	 */
	_popupEnabledLinkItems: [],

	/**
	 * Array of popup enabled Scroll Triggered Row
	 *
	 * @since 1.1.1
	 * @private
	 * @property {Array} _popupEnabledScrollToElementTriggeredRow
	 */
	_popupEnabledScrollToElementTriggeredRow: [],

	/**
	 * The current popup trigger. Used also to bind the timeout to
	 * in the triggerPopup method
	 *
	 * @since 1.0.0
	 * @access private
	 * @property {Array} _currentPopupTrigger
	 */
	_currentPopupTrigger: [],

	/**
	 * The current open popups
	 *
	 * [
	 *      'wpd-bb-popup-xxx' => { jBox instance }
	 * ]
	 *
	 * @since 1.0.6
	 * @access private
	 * @property {Array} _currentOpenPopups
	 */
	_currentOpenPopups: [],

	/**
	 * Starting scroll position in pixels
	 *
	 * @since 1.0.0
	 * @access private
	 * @property {int} _startingScrollPosition
	 */
	_startingScrollPosition: null,

	/**
	 * Cached popups
	 *
	 * @since 1.0.0
	 * @access private
	 * @property {Object} _cachedPopups
	 */
	_cachedPopups: {},

	/**
	 * An array of rules to check and run when a popup is
	 * opened
	 *
	 * @since 1.0.9
	 * @access private
	 * @property {Array} _queueOpenRules
	 */
	_queueOpenRules: [],

	/**
	 * An array of rules to check and run when a popup is
	 * closed
	 *
	 * @since 1.0.9
	 * @access private
	 * @property {Array} _queueCloseRules
	 */
	_queueCloseRules: [],

	/**
	 * @since 1.0.9
	 */
	_queueCloseRulesAction: [],

	/**
	 * @since 1.0.9
	 */
	_removableTriggersFromQueueOnceRun: {},

	/**
	 * @since 1.1.1
	 * @private
	 * @property {Array} _scrolledToElementHasRun
	 */
	_scrolledToElementHasRun: [],

	/**
	 * Set some variables at object level
	 *
	 * @since 1.0.0
	 * @access private
	 * @method _setVariables
	 */
	_setVariables () {
		WPDBBPopupsFrontEnd._startingScrollPosition = window.scrollY || window.pageYOffset
	},

	/**
	 * Gets all modules enabled for popups and stores them in
	 * WPDBBPopupsFrontEnd._popupEnabledButtonModules
	 *
	 * @since 1.0.0
	 * @access private
	 * @method _setPopupEnabledButtonModules
	 */
	_setPopupEnabledButtonModules () {
		WPDBBPopupsFrontEnd._popupEnabledButtonModules = [...document.querySelectorAll('.wpd-bb-popup__button--enabled')]
	},

	/**
	 * Gets all modules enabled for popups and stores them in WPDBBPopupsFrontEnd._popupEnabledLinkItems
	 *
	 * @since 1.1.0
	 * @private
	 * @method _setPopupEnabledLinks
	 */
	_setPopupEnabledLinks () {
		WPDBBPopupsFrontEnd._popupEnabledLinkItems = [...document.querySelectorAll('.wpd-bb-popup__link--enabled')]
	},

	/**
	 * Gets all modules enabled for popups and stores them in WPDBBPopupsFrontEnd._popupEnabledScrollToElementTriggeredRow
	 *
	 * @since 1.0.0
	 * @private
	 * @method _setPopupEnabledButtonModules
	 */
	_setScrollToElementTriggeredRows () {
		WPDBBPopupsFrontEnd._popupEnabledScrollToElementTriggeredRow = [...document.querySelectorAll('div[data-wpd-bb-scroll-trigger-popup-id]')]
	},
	/**
	 * @since 1.0.9
	 * @access private
	 * @method _setRemovableTriggersFromQueueOnceRun
	 */
	_setRemovableTriggersFromQueueOnceRun () {
		WPDBBPopupsFrontEnd._removableTriggersFromQueueOnceRun = ['exit', 'entrance', 'scroll', 'scrolledToElement']
	},

	/**
	 * @since 1.0.9
	 * @access private
	 * @method _setQueueOpenRules
	 */
	_setQueueOpenRules () {
		const openRules = [
			{
				type: 'modal',
				unbindOnTrigger: [
					{
						trigger: 'entrance',
						unbindMethods: [
							WPDBBPopupsFrontEnd._unbindExitPopupEvents
						]
					},
					{
						trigger: 'scrolled',
						unbindMethods: [
							WPDBBPopupsFrontEnd._unbindExitPopupEvents,
							WPDBBPopupsFrontEnd._unbindEntrancePopupEvents
						]
					},
					{
						trigger: 'exit',
						unbindMethods: [
							WPDBBPopupsFrontEnd._unbindEntrancePopupEvents
						]
					},
					{
						trigger: 'button',
						unbindMethods: [
							WPDBBPopupsFrontEnd._unbindExitPopupEvents,
							WPDBBPopupsFrontEnd._unbindEntrancePopupEvents
						]
					},
					{
						trigger: 'link',
						unbindMethods: [
							WPDBBPopupsFrontEnd._unbindExitPopupEvents,
							WPDBBPopupsFrontEnd._unbindEntrancePopupEvents
						]
					},
					{
						trigger: 'scrolledToElement',
						unbindMethods: [
							WPDBBPopupsFrontEnd._unbindExitPopupEvents,
							WPDBBPopupsFrontEnd._unbindEntrancePopupEvents
						]
					}
				]
			},
			{
				type: 'fly_out',
				unbindOnTrigger: [
					{
						trigger: 'any',
						unbindMethods: [
							WPDBBPopupsFrontEnd._unbindScrollPopupEvents,
							WPDBBPopupsFrontEnd._unbindExitPopupEvents,
							WPDBBPopupsFrontEnd._unbindEntrancePopupEvents
						]
					}
				]
			}
		]

		WPDBBPopupsFrontEnd._queueOpenRules = openRules
	},

	/**
	 * @since 1.0.9
	 * @access private
	 * @method _setQueueOpenRules
	 */
	_setQueueCloseRules () {
		const closeRules = [
			{
				type: 'modal',
				unbindOnTrigger: [
					{
						trigger: 'entrance',
						unbindMethods: [
							{
								handle: WPDBBPopupsFrontEnd._bindExitPopupEvents,
								param: null
							}
						]
					},
					{
						trigger: 'scrolled',
						unbindMethods: [
							{
								handle: WPDBBPopupsFrontEnd._bindExitPopupEvents,
								param: null
							},
							{
								handle: WPDBBPopupsFrontEnd._queueTrigger,
								param: 'entrance'
							},
							{
								handle: WPDBBPopupsFrontEnd._bindEntrancePopupEvents,
								param: true
							}
						]
					},
					{
						trigger: 'exit',
						unbindMethods: [
							{
								handle: WPDBBPopupsFrontEnd._queueTrigger,
								param: 'entrance'
							},
							{
								handle: WPDBBPopupsFrontEnd._bindEntrancePopupEvents,
								param: true
							}
						]
					},
					{
						trigger: 'button',
						unbindMethods: [
							{
								handle: WPDBBPopupsFrontEnd._bindExitPopupEvents,
								param: null
							},
							{
								handle: WPDBBPopupsFrontEnd._queueTrigger,
								param: 'entrance'
							},
							{
								handle: WPDBBPopupsFrontEnd._bindEntrancePopupEvents,
								param: true
							}
						]
					},
					{
						trigger: 'link',
						unbindMethods: [
							{
								handle: WPDBBPopupsFrontEnd._bindExitPopupEvents,
								param: null
							},
							{
								handle: WPDBBPopupsFrontEnd._queueTrigger,
								param: 'entrance'
							},
							{
								handle: WPDBBPopupsFrontEnd._bindEntrancePopupEvents,
								param: true
							}
						]
					},
					{
						trigger: 'scrolledToElement',
						unbindMethods: [
							{
								handle: WPDBBPopupsFrontEnd._bindExitPopupEvents,
								param: null
							},
							{
								handle: WPDBBPopupsFrontEnd._queueTrigger,
								param: 'entrance'
							},
							{
								handle: WPDBBPopupsFrontEnd._bindEntrancePopupEvents,
								param: true
							}
						]
					}
				]
			},
			{
				type: 'fly_out',
				unbindOnTrigger: [
					{
						trigger: 'any',
						unbindMethods: [
							{
								handle: WPDBBPopupsFrontEnd._bindScrollPopupEvents,
								param: null
							},
							{
								handle: WPDBBPopupsFrontEnd._bindExitPopupEvents,
								param: null
							},
							{
								handle: WPDBBPopupsFrontEnd._bindEntrancePopupEvents,
								param: true
							}
						]
					}
				]
			}
		]

		WPDBBPopupsFrontEnd._queueCloseRules = closeRules
	},

	/**
	 * @since 1.0.9
	 * @access private
	 * @method _setQueueOpenRules
	 */
	_setQueueCloseRulesAction () {
		WPDBBPopupsFrontEnd._queueCloseRulesAction = [WPDBBPopupsFrontEnd._setupButtonPopups, WPDBBPopupsFrontEnd._setupLinkPopups, WPDBBPopupsFrontEnd._setupScrollTriggeredRowPopups]
	},

	/**
	 * Stores the HTML for hidden popups on page load, to inject into DOM later
	 *
	 * @since 1.0.0
	 * @access private
	 * @method _cachePopupHTML
	 */
	_cachePopupHTML () {
		(WPDPopupConfig['activePopups'] || []).forEach(popup => {
			WPDBBPopupsFrontEnd._cachedPopups[popup.id] = document.getElementById(`${WPDPopupConfig.wpdPopupCpt}-${popup.id}__content`).innerHTML
		})
	},

	/**
	 * Add an event listener to the document for when the DOM is loaded to run the relevant trigger method
	 *
	 * @since 1.0.0
	 * @access _private
	 * @method _bindEntrancePopupEvents
	 * @param requeue Boolean to define whether to re-queue the entrance popup
	 *                if it's original trigger was missed (if another popup was open)
	 */
	_bindEntrancePopupEvents (requeue = false) {
		if (typeof WPDBBPopupsFrontEnd._willRunTriggers.entrance === 'object' && WPDBBPopupsFrontEnd._hasRunTriggers.indexOf('entrance') === -1) {
			if (requeue) {
				WPDBBPopupsFrontEnd._triggerEntrancePopup()
			} else {
				document.addEventListener('DOMContentLoaded', WPDBBPopupsFrontEnd._triggerEntrancePopup)
			}
		}
	},

	/**
	 * Remove event listener to the document for when the DOM is loaded to run the relevant trigger method
	 *
	 * @since 1.0.0
	 * @access _private
	 * @method _unbindEntrancePopupEvents
	 */
	_unbindEntrancePopupEvents (type = null) {
		clearTimeout(WPDBBPopupsFrontEnd._currentPopupTrigger['entrance'])
		document.removeEventListener('DOMContentLoaded', WPDBBPopupsFrontEnd._triggerEntrancePopup)
	},

	/**
	 * Add an event listener to the document for when we mouse out to run the relevant trigger method
	 *
	 * @since 1.0.0
	 */
	_bindExitPopupEvents () {
		if (typeof WPDBBPopupsFrontEnd._willRunTriggers.exit === 'object' && WPDBBPopupsFrontEnd._hasRunTriggers.indexOf('exit') === -1) {
			document.body.addEventListener('mouseout', WPDBBPopupsFrontEnd._triggerExitIntentPopup)
		}
	},

	/**
	 * Remove event listener to the document for when we mouse out to run the relevant trigger method
	 *
	 * @since 1.0.0
	 * @access _private
	 * @method _unbindExitPopupEvents
	 */
	_unbindExitPopupEvents (type = null) {
		document.body.removeEventListener('mouseout', WPDBBPopupsFrontEnd._triggerExitIntentPopup)
	},

	/**
	 * Add an event listener to each popup enabled click item to run the relevant trigger method
	 *
	 * @since 1.0.0
	 * @access _private
	 * @method _bindLinksPopupEvents
	 */
	_bindLinksPopupEvents () {
		if (WPDBBPopupsFrontEnd._popupEnabledLinkItems.length) {
			WPDBBPopupsFrontEnd._popupEnabledLinkItems.forEach(item => {
				item.addEventListener('click', function (e) {
					WPDBBPopupsFrontEnd._triggerLinkPopup(e)
				})
			})
		}
	},

	/**
	 * Add an event listener to the window for scroll events to run the relevant trigger method
	 *
	 * @since 1.1.1
	 * @access _private
	 * @method _bindScrollToElementTriggeredPopupEvents
	 */
	_bindScrollToElementTriggeredPopupEvents () {
		if (WPDBBPopupsFrontEnd._popupEnabledScrollToElementTriggeredRow.length) {
			WPDBBPopupsFrontEnd._popupEnabledScrollToElementTriggeredRow.forEach(module => {
				let scrollPosition
				if (module.dataset.wpdBbPopupRowElementPosition) {
					scrollPosition = module.dataset.wpdBbPopupRowElementPosition
				}
				let offset = 0
				switch (scrollPosition) {
					case 'element_bottom' : {
						offset = '100%'
						break
					}
					case 'element_middle' : {
						offset = '50%'
						break
					}
					case 'element_top' : {
						offset = 0
						break
					}
					default : {
						offset = 0
						break
					}
				}
				const waypoints = new Waypoint({
					element: module,
					handler: debounce((direction) => {
						if (direction === 'down') {
							WPDBBPopupsFrontEnd._triggerScrollToElementTriggeredPopup(module)
						}
					}, 100),
					offset: offset
				})
			})
		}
	},

	/**
	 * Add an event listener to the window for scroll events to run the relevant trigger method
	 *
	 * @since 1.0.0
	 * @access _private
	 * @method _bindScrollPopupEvents
	 */
	_bindScrollPopupEvents () {
		if (typeof WPDBBPopupsFrontEnd._willRunTriggers.scroll === 'object' && WPDBBPopupsFrontEnd._hasRunTriggers.indexOf('scroll') === -1) {
			window.addEventListener('scroll', debounce(WPDBBPopupsFrontEnd._triggerScrollPopup, 50))
		}
	},

	/**
	 * Remove event listener to the window for scroll events to run the relevant trigger method
	 *
	 * @since 1.0.0
	 * @access _private
	 * @method _unbindScrollPopupEvents
	 */
	_unbindScrollPopupEvents (type = null) {
		window.removeEventListener('scroll', WPDBBPopupsFrontEnd._triggerScrollPopup)
	},

	/**
	 * Add an event listener to each popup enabled button to run the relevant trigger method
	 *
	 * @since 1.0.0
	 * @access _private
	 * @method _bindButtonPopupEvents
	 */
	_bindButtonPopupEvents () {
		if (WPDBBPopupsFrontEnd._popupEnabledButtonModules.length) {
			WPDBBPopupsFrontEnd._popupEnabledButtonModules.forEach(module => {
				module.querySelector('a').addEventListener('click', function (e) {
					WPDBBPopupsFrontEnd._triggerButtonPopup(e)
				})
			})
		}
	},

	/**
	 * Loops through the types of triggered popups and sets them up
	 *
	 * @since 1.0.0
	 * @access private
	 * @method _setupTriggeredPopups
	 */
	_setupTriggeredPopups () {
		['exit', 'entrance', 'scroll'].forEach((trigger) => WPDBBPopupsFrontEnd._queueTrigger(trigger))
	},

	/**
	 * Store all popups that are triggered by a link
	 *
	 * @since 1.1.1
	 * @private
	 * @method _setupLinkPopups
	 */
	_setupLinkPopups () {
		// Create new object literal in willRunTriggers
		WPDBBPopupsFrontEnd._willRunTriggers['link'] = {}

		WPDBBPopupsFrontEnd._popupEnabledLinkItems.forEach((item) => {
			const id = parseInt(item.dataset.wpdBbPopupId)
			const popup = (WPDPopupConfig.activePopups || []).find(popup => popup.id === id)

			item.dataset.wpdBbPopupId = id

			WPDBBPopupsFrontEnd._setupLinkPopup(popup)
		})
	},

	/**
	 * Set trigger and instantiate popup object in _wilLRun.links
	 *
	 * @since 1.1.1
	 * @private
	 * @method _setupLinkPopup
	 */
	_setupLinkPopup (popup) {
		/**
		 * Here we force the trigger to be 'link', as if we have queued a popup that
		 * has a trigger AND is included via a button, there will be a trigger set in
		 * the object, and this popup object will inherit that.
		 *
		 * Ie. if a button triggers a popup that is configured using a 'scroll' trigger,
		 * scroll would have been passed through. Now it is 'button'
		 *
		 * To do this, we need to create a clone of the popup object, as JS will otherwise
		 * pass the object by reference (so if we change the trigger on this popup object
		 * it can cause other objects to also have their trigger type changed)
		 */
		const copiedPopup = cloneDeep(popup)

		if (copiedPopup) {
			// Manually set the trigger type
			copiedPopup.trigger = 'link'

			// Add a popup instance
			copiedPopup['Jbox'] = new Jbox('Modal', WPDBBPopupsFrontEnd._getPopupParameters(copiedPopup, 'link'))

			// Add popup to button array in willRunTriggers
			WPDBBPopupsFrontEnd._willRunTriggers.link[copiedPopup.id] = copiedPopup
		}
	},

	/**
	 * Instantiates a popup object and stores in _willRunTriggers
	 *
	 * @since 1.1.1
	 * @private
	 * @method _setupScrollTriggeredRowPopups
	 */
	_setupScrollTriggeredRowPopups () {
		// Create new object literal in willRunTriggers
		WPDBBPopupsFrontEnd._willRunTriggers['scrolledToElement'] = {}

		WPDBBPopupsFrontEnd._popupEnabledScrollToElementTriggeredRow.forEach((item) => {
			const id = parseInt(item.dataset.wpdBbScrollTriggerPopupId)
			const popup = (WPDPopupConfig.activePopups || []).find(popup => popup.id === id)

			WPDBBPopupsFrontEnd._setupScrollPopup(popup)
		})
	},

	/**
	 * Set trigger and instantiate popup object in _willRun.links
	 *
	 * @since 1.0.0
	 * @private
	 * @method _setupLinkPopup
	 */
	_setupScrollPopup (popup) {
		/**
		 * Here we force the trigger to be 'link', as if we have queued a popup that
		 * has a trigger AND is included via a button, there will be a trigger set in
		 * the object, and this popup object will inherit that.
		 *
		 * Ie. if a button triggers a popup that is configured using a 'scroll' trigger,
		 * scroll would have been passed through. Now it is 'button'
		 *
		 * To do this, we need to create a clone of the popup object, as JS will otherwise
		 * pass the object by reference (so if we change the trigger on this popup object
		 * it can cause other objects to also have their trigger type changed)
		 */
		const copiedPopup = cloneDeep(popup)

		if (copiedPopup) {
			// Manually set the trigger type
			copiedPopup.trigger = 'scrolledToElement'

			// Add a popup instance
			copiedPopup['Jbox'] = new Jbox('Modal', WPDBBPopupsFrontEnd._getPopupParameters(copiedPopup, 'scrolledToElement'))
			// Add popup to button array in willRunTriggers
			WPDBBPopupsFrontEnd._willRunTriggers.scrolledToElement[copiedPopup.id] = copiedPopup
		}
	},

	/**
	 * Instantiates a popup object and stores in _willRunTriggers
	 *
	 * @since 1.0.0
	 * @access private
	 * @method _queueTrigger
	 * @param trigger String value of trigger type (eg entrance, scroll, exit)
	 */
	_queueTrigger (trigger) {
		// Find the relevant popup
		const popup = (WPDPopupConfig.activePopups || []).find(popup => popup.trigger === trigger)
		const copiedPopup = cloneDeep(popup)

		if (copiedPopup) {
			// Add a Jbox instance to it
			copiedPopup['Jbox'] = new Jbox('Modal', WPDBBPopupsFrontEnd._getPopupParameters(copiedPopup))
			// Push it to the _willRunTriggers array
			WPDBBPopupsFrontEnd._willRunTriggers[trigger] = copiedPopup
		}
	},

	/**
	 * Store all popups that are triggered by a button
	 *
	 * @since 1.0.0
	 * @access private
	 * @method _setupButtonPopups
	 */
	_setupButtonPopups () {
		// Create new object literal in willRunTriggers
		WPDBBPopupsFrontEnd._willRunTriggers['buttons'] = {}

		WPDBBPopupsFrontEnd._popupEnabledButtonModules.forEach((module) => {
			const id = parseInt(module.dataset.wpdBbPopupId)
			const popup = (WPDPopupConfig.activePopups || []).find(popup => popup.id === id)
			let button = module.querySelector('a')

			button.dataset.wpdBbPopupId = id

			WPDBBPopupsFrontEnd._setupButtonPopup(popup)
		})
	},

	/**
	 * Set trigger and instantiate popup object in _wilLRun.buttons
	 *
	 * @since 1.0.0
	 * @access private
	 * @method _setupButtonPopup
	 */
	_setupButtonPopup (popup) {
		/**
		 * Here we force the trigger to be 'button', as if we have queued a popup that
		 * has a trigger AND is included via a button, there will be a trigger set in
		 * the object, and this popup object will inherit that.
		 *
		 * Ie. if a button triggers a popup that is configured using a 'scroll' trigger,
		 * scroll would have been passed through. Now it is 'button'
		 *
		 * To do this, we need to create a clone of the popup object, as JS will otherwise
		 * pass the object by reference (so if we change the trigger on this popup object
		 * it can cause other objects to also have their trigger type changed)
		 */
		const copiedPopup = cloneDeep(popup)

		if (copiedPopup) {
			// Manually set the trigger type
			copiedPopup.trigger = 'button'

			// Add a popup instance
			copiedPopup['Jbox'] = new Jbox('Modal', WPDBBPopupsFrontEnd._getPopupParameters(copiedPopup, 'button'))

			// Add popup to button array in willRunTriggers
			WPDBBPopupsFrontEnd._willRunTriggers.buttons[copiedPopup.id] = copiedPopup
		}
	},

	/**
	 * Add an event listener to each close popup enabled button to run the relevant trigger method
	 *
	 * @since 1.0.6
	 * @access _private
	 * @method _bindButtonClosePopupEvents
	 */
	_bindButtonClosePopupEvents () {
		document.addEventListener('click', function (e) {
			if (e.target.tagName !== 'A' && e.target.tagName !== 'SPAN' && e.target.tagName !== 'BUTTON') {
				return
			}

			const closestModule = e.target.closest('.wpd-bb-popup__close-button') || null

			if (closestModule) {
				WPDBBPopupsFrontEnd._triggerButtonClosePopup(e)
			}
		}, false)
	},

	/**
	 * Set popup params
	 *
	 * @since 1.0.0
	 * @access private
	 * @method _getPopupParameters
	 * @param popup Object
	 * @param forceTrigger String to forcefully specify the trigger
	 */
	_getPopupParameters (popup, forceTrigger = null) {
		const trigger = forceTrigger || popup.trigger
		const popupContent = document.getElementById(`${WPDPopupConfig.wpdPopupCpt}-${popup.id}__content`)

		return {

			/**
			 * Set the content element
			 */
			content: popupContent.innerHTML,

			/**
			 * Set the ID
			 */
			id: `${WPDPopupConfig.wpdPopupCpt}-${popup.id}`,

			/**
			 * Add a class to the wrapper
			 */
			addClass: `${WPDPopupConfig.wpdPopupCpt}-${popup.id}__wrap`,

			/**
			 * Set popup dimensions
			 */
			width: WPDBBPopupsFrontEnd._getPopupWidth(popup.settings),
			height: popup.settings.popup_type !== 'fly_out' && popup.settings.height ? popup.settings.height : 'auto',
			maxHeight: WPDBBPopupsFrontEnd._getPopupMaxHeight(popup.settings),

			/**
			 * Set an offset to clear the close icon
			 */
			offset: {
				y: document.body.classList.contains('admin-bar') ? 32 : 0
			},

			/**
			 * Set the position & attributes
			 */
			position: {
				x: popup.settings.popup_type === 'fly_out' ? popup.settings.fly_out_x_position : 'center',
				y: popup.settings.popup_type === 'fly_out' ? popup.settings.fly_out_y_position : 'center'
			},

			/**
			 * Configure the design
			 */
			overlay: popup.settings.popup_type === 'modal',
			zIndex: popup.settings.popup_type === 'modal' ? 30000 : 29000,

			/**
			 * Configure animations
			 */
			animation: WPDBBPopupsFrontEnd._getAnimationParameters(popup),

			/**
			 * Configure scroll attributes
			 */
			isolateScroll: popup.settings.popup_type !== 'fly_out' && popup.settings.block_browser_scroll !== 'no',
			blockScroll: popup.settings.popup_type !== 'fly_out' && popup.settings.block_browser_scroll !== 'no',

			/**
			 * Set close button position
			 */
			closeButton: WPDBBPopupsFrontEnd._getCloseButton(popup.settings),

			/**
			 * Set close on overlay click
			 */
			closeOnClick: WPDBBPopupsFrontEnd._getCloseOnClickParameters(popup.settings),

			/**
			 * Callback methods
			 */
			onOpen: () => {
				/**
				 * Empty the original container so we don't get issues with duplicate input fields inside the popup
				 */
				popupContent.innerHTML = ''

				/**
				 * Get the layout's partial JS and execute it
				 *
				 * By doing it this way, the script is executed once it's downloaded which helps
				 * with JS components rendering
				 *
				 * On the callback, we then trigger a window resize event, and manually reinit
				 * some JS components
				 */
				WPDBBPopupsFrontEnd._firePopupBeaverBuilderLayoutScript(popup, () => {
					/**
					 * Dispatch a resize event
					 */
					const event = document.createEvent('HTMLEvents')
					event.initEvent('resize', true, false)
					document.dispatchEvent(event)

					/**
					 * Re-init animations
					 */
					if (typeof FLBuilderLayout !== 'undefined') {
						FLBuilderLayout._initModuleAnimations()
					}

					/**
					 * Dispatch a custom event for WPD Optimised Video
					 */
					const popupOpenEvent = new CustomEvent('WPDBBPopupOpen', {
						'detail': {
							'popupId': popup.id
						}
					})
					document.dispatchEvent(popupOpenEvent)
				})

				/**
				 * Re-init the queue
				 */
				WPDBBPopupsFrontEnd._reinitQueue(trigger, popup.settings.popup_type)

				/**
				 * Maybe set a cookie
				 */
				if (typeof popup.cookieTrigger === 'undefined' || popup.cookieTrigger === 'open') {
					WPDBBPopupsFrontEnd._setCookie(trigger, popup)
				}

				/**
				 * Log impression to post meta for logged out users
				 *
				 * @todo make this togglable
				 */
				if (!document.body.classList.contains('admin-bar')) {
					jQuery.get(`${WPDPopupConfig.ajaxUrl}`, {
						action: 'wpd_bb_popups_count_popup_impression',
						nonce: `${WPDPopupConfig.nonce}`,
						popupId: popup.id
					})
				}
			},

			onClose: () => {
				// Remove the popup from the _currentOpenPopups array
				WPDBBPopupsFrontEnd._currentOpenPopups = WPDBBPopupsFrontEnd._currentOpenPopups.filter(item => item.id !== `${WPDPopupConfig.wpdPopupCpt}-${popup.id}`)

				// Maybe set a cookie
				if (typeof popup.cookieTrigger !== 'undefined' && popup.cookieTrigger === 'close') {
					WPDBBPopupsFrontEnd._setCookie(trigger, popup)
				}
			},

			_onCloseComplete: () => {
				// Remove node (which stops audio/video etc)
				const element = document.getElementById(`${WPDPopupConfig.wpdPopupCpt}-${popup.id}`)
				const parent = element.parentNode
				parent.removeChild(element)

				// Re-add HTML from cache
				popupContent.innerHTML = WPDBBPopupsFrontEnd._cachedPopups[popup.id]

				// Re-init popup queue
				WPDBBPopupsFrontEnd._reinitQueue(trigger, popup.settings.popup_type, 'close')
			}
		}
	},

	/**
	 * Get the width for this popup
	 *
	 * @since 1.0.9
	 * @access private
	 * @method _getPopupWidth
	 * @param settings Settings object of popup
	 */
	_getPopupWidth (settings) {
		if (settings.hasOwnProperty('is_full_width') && settings.is_full_width === 'true') {
			return window.outerWidth
		}

		return settings.width
	},

	/**
	 * Get the max height for this popup
	 *
	 * @since 1.0.6
	 * @access private
	 * @method _getPopupMaxHeight
	 * @param settings Settings object of popup
	 */
	_getPopupMaxHeight (settings) {
		if (WPDBBPopupsFrontEnd._getCloseButton(settings)) {
			return document.body.classList.contains('admin-bar') ? window.innerHeight - ((parseInt(settings.close_icon_size) + 32) * 2) : window.innerHeight - (parseInt(settings.close_icon_size) * 2)
		} else {
			return document.body.classList.contains('admin-bar') ? window.innerHeight : window.innerHeight
		}
	},

	/**
	 * Get the button details for this popup
	 *
	 * @since 1.0.6
	 * @access private
	 * @method _getCloseButton
	 * @param settings Settings object of popup
	 */
	_getCloseButton (settings) {
		if (settings.modal_disable_close_icon && settings.modal_disable_close_icon === 'no') {
			return settings.popup_type === 'modal' ? settings.modal_close_icon_position : settings.fly_out_close_icon_position
		} else {
			return false
		}
	},

	/**
	 * Get the close on click parameters
	 *
	 * @since 1.0.7
	 * @access private
	 * @method _getCloseOnClickParameters
	 * @param settings Settings object of popup
	 */
	_getCloseOnClickParameters (settings) {
		if (settings.popup_type === 'fly_out') {
			return false
		} else if (settings.popup_type === 'modal') {
			if (!settings.modal_close_on_overlay_click || settings.modal_close_on_overlay_click && settings.modal_close_on_overlay_click === 'yes') {
				return 'overlay'
			}
		}

		return false
	},

	/**
	 * Get the animation params for the popup
	 *
	 * @since 1.0.0
	 * @access private
	 * @method _getAnimationParameters
	 * @param popup Popup object
	 */
	_getAnimationParameters (popup) {
		let open, close

		if (typeof popup.settings.open_animation !== 'undefined') {
			open = popup.settings.open_animation

			if (popup.settings.open_animation_direction !== 'undefined' && popup.settings.open_animation === 'slide' || popup.settings.open_animation === 'move') {
				open = `${open}:${popup.settings.open_animation_direction}`
			}
		}

		if (typeof popup.settings.close_animation !== 'undefined') {
			close = popup.settings.close_animation

			if (popup.settings.close_animation_direction !== 'undefined' && popup.settings.close_animation === 'slide' || popup.settings.close_animation === 'move') {
				close = `${close}:${popup.settings.close_animation_direction}`
			}
		}

		return { open, close }
	},

	/**
	 * Get the script for a popup layout
	 * @since 1.9.1
	 * @param popup
	 * @param callback
	 */
	_firePopupBeaverBuilderLayoutScript (popup, callback) {
		jQuery.getScript(popup.script.source, () => {
			callback()
		})
	},

	/**
	 * Run when any popup is opened or closed. Designed to prevent popups from triggering
	 * more than they should and clashing.
	 *
	 * @since 1.0.0
	 * @access private
	 * @method _reinitQueue
	 * @param trigger String of trigger type (eg. entrance, exit, scroll)
	 * @param type String of popup type (eg. modal, fly out)
	 * @param action String of popup action - was it opened or closed?
	 */
	_reinitQueue (trigger, type, action = 'open') {
		if (action === 'open') {
			WPDBBPopupsFrontEnd._hasRunTriggers.push(trigger)

			const openRules = WPDBBPopupsFrontEnd._queueOpenRules.filter((rule) => rule.type === type)

			openRules[0].unbindOnTrigger.forEach((handle) => {
				if (handle.trigger === trigger) {
					handle.unbindMethods.forEach((method) => {
						method()
					})
				}
			})
			WPDBBPopupsFrontEnd._removableTriggersFromQueueOnceRun.forEach((triggerType) => {
				if (trigger === triggerType) {
					WPDBBPopupsFrontEnd._willRunTriggers[trigger] = null
				}
			})
		}

		if (action === 'close') {
			const closeRules = WPDBBPopupsFrontEnd._queueCloseRules.filter((rule) => rule.type === type)

			closeRules[0].unbindOnTrigger.forEach((handle) => {
				if (handle.trigger === trigger) {
					handle.unbindMethods.forEach((method) => {
						if (method.param) {
							method.handle(method.param)
						} else {
							method.handle()
						}
					})
				}
			})
			WPDBBPopupsFrontEnd._queueCloseRulesAction.forEach((method) => {
				method()
			})

			/**
			 * Re-setting up buttons on close fixes an edge case
			 * @todo look into this further
			 *
			 * To replicate issue, comment the method below
			 * 1 - Assign an exit modal
			 * 2 - Add a button with the same modal popup assigned
			 * 3 - Click button (modal should open)
			 * 4 - Open exit modal by moving mouse upwards
			 * 5 - Click button again - there'll be no content
			 *
			 */
		}
	},

	/**
	 * Set cookie
	 *
	 * @since 1.0.4
	 * @access private
	 * @method _setCookie
	 * @param trigger string Name of trigger
	 * @param popup object Popup object
	 */
	_setCookie: function (trigger, popup) {
		if (trigger !== 'button' && popup.displayPopupMaximumTimes) {
			let cookieDisplayedCount = Cookie.get(`${WPDPopupConfig.wpdPopupCpt}-${popup.id}--${popup.scope}--${trigger}`) || 0
			let cookieMaxTimes = popup.displayPopupMaximumTimes
			let cookieDuration = popup.hidePopupForNumberOfDays || 7

			if (cookieMaxTimes >= cookieDisplayedCount) {
				Cookie.set(`${WPDPopupConfig.wpdPopupCpt}-${popup.id}--${popup.scope}--${trigger}`, parseInt(cookieDisplayedCount) + 1, {
					expires: parseInt(cookieDuration)
				})
			}
		}
	},

	_setForcedBlock: function (popup) {
		if (popup.trigger !== 'button' || popup.trigger !== 'link') {
			const cookieDuration = 7
			Cookie.set(`${WPDPopupConfig.wpdPopupCpt}-${popup.id}`, '__wpd_hide', {
				expires: parseInt(cookieDuration)
			})
		}
	},

	/**
	 * Check if a popup can be opened
	 *
	 * @since 1.0.4
	 * @access private
	 * @method _canShowPopup
	 * @param popup The popup object to open
	 */
	_canShowPopup (popup) {
		/**
		 * Check for body class, perhaps used by plugins, such
		 * as CSS Hero, Yellow Pencil, Microthemer
		 */
		if (document.body.classList.contains('yp-yellow-pencil')) {
			return false
		}
		/*
        * Check for the popup setting for the device
         * and Block if the setting is set to false.
         */
		if (popup && popup.settings.popup_disable_on_devices !== 'null' && popup.settings.popup_disable_on_devices.includes(deviceDetector())) {
			return false
		}

		const cookieHidePopup = Cookie.get(`${WPDPopupConfig.wpdPopupCpt}-${popup.id}`) || 0

		if (cookieHidePopup === '__wpd_hide') {
			return false
		}

		/**
		 * Check cookies
		 */
		let cookieDisplayedCount = Cookie.get(`${WPDPopupConfig.wpdPopupCpt}-${popup.id}--${popup.scope}--${popup.trigger}`) || 0
		let displayPopupMaxTimes = popup.displayPopupMaximumTimes || 0

		return displayPopupMaxTimes === 0 || parseInt(cookieDisplayedCount) < parseInt(displayPopupMaxTimes)
	},

	/**
	 * Fire an entrance popup
	 *
	 * @since 1.0.0
	 * @access private
	 * @method _triggerEntrancePopup
	 */
	_triggerEntrancePopup () {
		const popup = WPDBBPopupsFrontEnd._willRunTriggers.entrance

		if (!popup) {
			return
		}

		// Trigger the popup
		WPDBBPopupsFrontEnd.triggerPopup(popup, popup.delay * 1000)
	},

	/**
	 * Fire a scroll popup
	 *
	 * @since 1.0.0
	 * @access private
	 * @method _triggerScrollPopup
	 */
	_triggerScrollPopup () {
		const popup = WPDBBPopupsFrontEnd._willRunTriggers.scroll

		if (!popup) {
			return
		}

		const windowHeight = window.outerHeight
		const percent = popup.depth / 100
		const scrollY = window.scrollY || window.pageYOffset

		// If we scroll up or down 50 pixels
		if (Math.abs(scrollY - WPDBBPopupsFrontEnd._startingScrollPosition) >= 50 && scrollY > (document.body.clientHeight * percent) - (windowHeight / 2)) {
			WPDBBPopupsFrontEnd.triggerPopup(popup)
		}
	},

	/**
	 * Fire an exit intent popup
	 *
	 * @since 1.0.0
	 * @access private
	 * @method _triggerExitIntentPopup
	 * @param e Event object
	 */
	_triggerExitIntentPopup (e) {
		const popup = WPDBBPopupsFrontEnd._willRunTriggers.exit
		const scrollPosition = window.pageYOffset || document.documentElement.scrollTop

		if (!popup) {
			return
		}

		if ((e.pageY - scrollPosition) < 5) {
			WPDBBPopupsFrontEnd.triggerPopup(popup, 0)
		}
	},

	/**
	 * Bind click event to open a specific modal
	 *
	 * @since 1.0.0
	 * @access private
	 * @method _triggerButtonPopup
	 * @param e Event object
	 */
	_triggerButtonPopup (e) {
		// Prevent default click action
		e.preventDefault()

		let popupId

		if (e.target.classList.contains('fl-button-text') || e.target.classList.contains('pp-button-text') || e.target.classList.contains('uabb-button-text') || e.target.classList.contains('fl-button-icon')) {
			popupId = e.target.parentNode.dataset.wpdBbPopupId
		} else if (e.target.classList.contains('fl-button') || e.target.classList.contains('pp-button') || e.target.classList.contains('uabb-button')) {
			popupId = e.target.dataset.wpdBbPopupId
		} else {
			return
		}

		// Get the popup object
		const popup = WPDBBPopupsFrontEnd._willRunTriggers.buttons[popupId]

		// Fire it
		WPDBBPopupsFrontEnd.triggerPopup(popup)
	},
	/**
	 * Open a popup
	 *
	 * @since 1.0.0
	 * @access public
	 * @method triggerPopup
	 * @param popup The popup object to open
	 * @param time An option delay to open the popup
	 */
	triggerPopup (popup, time = 0) {
		if (WPDBBPopupsFrontEnd._canShowPopup(popup)) {
			/**
			 * Add the Jbox instance to a global array that we'll use later to close the popup
			 * using a custom button element
			 *
			 * @type {jBox}
			 */
			WPDBBPopupsFrontEnd._currentOpenPopups[popup.Jbox.id] = popup.Jbox

			/**
			 * Trigger the popup with an optional timeout
			 *
			 * @type {method}
			 */
			WPDBBPopupsFrontEnd._currentPopupTrigger[popup.trigger] = setTimeout(() => popup.Jbox.open(), time)
		}
	},

	/**
	 * Bind click event to close a specific modal
	 *
	 * @since 1.0.6
	 * @access private
	 * @method _triggerButtonClosePopup
	 * @param e Event object
	 */
	_triggerButtonClosePopup (e) {
		e.preventDefault()

		// Get the closest popup from the target
		const closestPopupId = e.target.closest('.jBox-wrapper').id

		const closestPopupHideModule = e.target.closest('.wpd-bb-popup__close-button--hide-popup') || null

		if (closestPopupHideModule) {
			const popupId = parseInt(closestPopupId.substring('wpd-bb-popup-'.length))
			const popup = (WPDPopupConfig.activePopups || []).find(popup => popup.id === popupId)
			WPDBBPopupsFrontEnd._setForcedBlock(popup)
		}

		WPDBBPopupsFrontEnd._currentOpenPopups[closestPopupId].close()
	},

	/**
	 * Bind click event to open a specific modal for link items
	 *
	 * @since 1.0.0
	 * @private
	 * @param e Event object
	 */
	_triggerLinkPopup (e) {
		e.preventDefault()

		let popupId

		if (e.target.classList.contains('wpd-bb-popup__link--enabled')) {
			popupId = e.target.dataset.wpdBbPopupId
		} else {
			return
		}

		// Get the popup object
		const popup = WPDBBPopupsFrontEnd._willRunTriggers.link[popupId]

		// Fire it
		WPDBBPopupsFrontEnd.triggerPopup(popup)
	},

	/**
	 * Trigger popup
	 *
	 * @param e
	 * @private
	 */
	_triggerScrollToElementTriggeredPopup (e) {
		const popupId = e.dataset.wpdBbScrollTriggerPopupId
		const hasRunPopup = popupId + '-' + e.dataset.node
		if (popupId && WPDBBPopupsFrontEnd._willRunTriggers.scrolledToElement !== null && !WPDBBPopupsFrontEnd._scrolledToElementHasRun.includes(hasRunPopup)) {
			WPDBBPopupsFrontEnd._scrolledToElementHasRun.push(hasRunPopup)
			// Get the popup object
			const popup = WPDBBPopupsFrontEnd._willRunTriggers.scrolledToElement[popupId]

			// Fire it
			WPDBBPopupsFrontEnd.triggerPopup(popup)
		}
	},

	/**
	 * Emit an event that we listen for within integrations which
	 * contains an object (passed by reference) of WPDBBPopupsFrontEnd
	 *
	 * @since 1.0.9
	 * @access private
	 * @method _setupIntegrations
	 */
	_setupIntegrations () {
		document.addEventListener('WPDBBPopupsSetupIntegrations', (e) => {
			const integration = e.detail.data
			if (typeof integration.init !== 'undefined') {
				integration._baseModule = WPDBBPopupsFrontEnd
			}
		}, false)
	},

	/**
	 * Initializes the WPD popup options
	 *
	 * @since 1.0.0
	 * @access public
	 * @method init
	 */
	init () {
		WPDBBPopupsFrontEnd._setupIntegrations()
		WPDBBPopupsFrontEnd._setVariables()
		WPDBBPopupsFrontEnd._setPopupEnabledButtonModules()
		WPDBBPopupsFrontEnd._setScrollToElementTriggeredRows()
		WPDBBPopupsFrontEnd._setRemovableTriggersFromQueueOnceRun()
		WPDBBPopupsFrontEnd._cachePopupHTML()
		WPDBBPopupsFrontEnd._setupTriggeredPopups()
		WPDBBPopupsFrontEnd._setPopupEnabledLinks()
		WPDBBPopupsFrontEnd._setupButtonPopups()
		WPDBBPopupsFrontEnd._setupLinkPopups()
		WPDBBPopupsFrontEnd._setupScrollTriggeredRowPopups()
		WPDBBPopupsFrontEnd._bindEntrancePopupEvents()
		WPDBBPopupsFrontEnd._bindExitPopupEvents()
		WPDBBPopupsFrontEnd._bindScrollPopupEvents()
		WPDBBPopupsFrontEnd._bindButtonPopupEvents()
		WPDBBPopupsFrontEnd._bindButtonClosePopupEvents()
		WPDBBPopupsFrontEnd._bindLinksPopupEvents()
		WPDBBPopupsFrontEnd._bindScrollToElementTriggeredPopupEvents()
		WPDBBPopupsFrontEnd._setQueueCloseRulesAction()
		WPDBBPopupsFrontEnd._setQueueOpenRules()
		WPDBBPopupsFrontEnd._setQueueCloseRules()
	}
}

WPDBBPopupsFrontEnd.init()
