function _php_cast_float (value) { 
  var type = typeof value
  switch (type) {
    case 'number':
      return value
    case 'string':
      return parseFloat(value) || 0
    case 'boolean':
    default:
      return require('./_php_cast_int')(value)
  }
}
