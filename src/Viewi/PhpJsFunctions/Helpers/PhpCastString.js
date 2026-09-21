function _phpCastString(value) { // eslint-disable-line camelcase
  // PHP's (string): true → '1', false/null → '', arrays → 'Array'. Floats print the way PHP
  // does (precision 14): 0.1 + 0.2 → '0.3', 1e20 → '1.0E+20', 1.5e-5 → '1.5E-5', -0.0 → '-0'.
  // An integral number within the safe range prints as an integer (JS can't tell 1 from 1.0).
  switch (typeof value) {
    case 'boolean':
      return value ? '1' : ''
    case 'string':
      return value
    case 'number': {
      if (isNaN(value)) {
        return 'NAN'
      }
      if (!isFinite(value)) {
        return (value < 0 ? '-' : '') + 'INF'
      }
      if (Object.is(value, -0)) {
        return '-0'
      }
      if (Number.isSafeInteger(value)) {
        return String(value)
      }
      const rounded = Number(value.toPrecision(14))
      const parts = rounded.toExponential(13).split('e')
      const exponent = parseInt(parts[1], 10)
      if (exponent < -4 || exponent >= 14) {
        let mantissa = parts[0].replace(/\.?0+$/, '')
        if (mantissa.indexOf('.') === -1) {
          mantissa += '.0'
        }
        return mantissa + 'E' + (exponent < 0 ? '-' : '+') + Math.abs(exponent)
      }
      return String(rounded)
    }
    case 'undefined':
      return ''
    case 'object':
      if (value === null) {
        return ''
      }
      return Array.isArray(value) ? 'Array' : 'Object'
  }
  throw new Error('Unsupported value type')
}
