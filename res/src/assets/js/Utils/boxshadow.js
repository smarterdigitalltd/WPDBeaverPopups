import hex2rgba from 'hex2rgba'

export function createBoxShadow (params) {
    const { horizontal = 0, vertical = 0, spread = 0, blur = 0, color = '000', opacity = 0.5 } = params

	return `${horizontal}px ${vertical}px ${spread}px ${blur}px ${hex2rgba(color, opacity)}`
}
