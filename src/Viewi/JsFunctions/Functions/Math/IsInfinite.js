function is_infinite (val) { 
  var warningType = ''
  if (val === Infinity || val === -Infinity) {
    return true
  }
  if (typeof val === 'object') {
    warningType = (Object.prototype.toString.call(val) === '[object Array]' ? 'array' : 'object')
  } else if (typeof val === 'string' && !val.match(/^[+-]?\d/)) {
    warningType = 'string'
  }
  if (warningType) {
    var msg = 'Warning: is_infinite() expects parameter 1 to be double, ' + warningType + ' given'
    throw new Error(msg)
  }
  return false
}
