function _phpCastString (value) {
  var type = typeof value
  switch (type) {
    case 'boolean':
      return value ? '1' : ''
    case 'string':
      return value
    case 'number':
      if (isNaN(value)) {
        return 'NAN'
      }
      if (!isFinite(value)) {
        return (value < 0 ? '-' : '') + 'INF'
      }
      return value + ''
    case 'undefined':
      return ''
    case 'object':
      if (Array.isArray(value)) {
        return 'Array'
      }
      if (value !== null) {
        return 'Object'
      }
      return ''
    case 'function':
    default:
      throw new Error('Unsupported value type')
  }
}
