export default function deviceByBreakpoint () {
	let device = 'desktop'

	if (FLBuilderLayoutConfig !== undefined) {
		if (window.outerWidth < FLBuilderLayoutConfig.breakpoints.small) {
			device = 'mobile'
		}

		if (window.outerWidth >= FLBuilderLayoutConfig.breakpoints.small && window.outerWidth < FLBuilderLayoutConfig.breakpoints.medium) {
			device = 'tablet'
		}
	}
	return device
}
