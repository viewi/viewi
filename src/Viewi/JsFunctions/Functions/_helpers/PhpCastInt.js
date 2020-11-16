function _php_cast_int (value) { 
  var type = typeof value
  switch (type) {
    case 'number':
      if (isNaN(value) || !isFinite(value)) {
        return 0
      }
      return value < 0 ? Math.ceil(value) : Math.floor(value)
    case 'string':
      return parseInt(value, 10) || 0
    case 'boolean':
    default:
      return +!!value
  }
}
