function is_nan (val) { 
  var warningType = ''
  if (typeof val === 'number' && isNaN(val)) {
    return true
  }
  if (typeof val === 'object') {
    warningType = (Object.prototype.toString.call(val) === '[object Array]' ? 'array' : 'object')
  } else if (typeof val === 'string' && !val.match(/^[+-]?\d/)) {
    warningType = 'string'
  }
  if (warningType) {
    throw new Error('Warning: is_nan() expects parameter 1 to be double, ' + warningType + ' given')
  }
  return false
}
