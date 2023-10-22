function is_nan (val) { // eslint-disable-line camelcase
  //  discuss at: https://locutus.io/php/is_nan/
  // original by: Onno Marsman (https://twitter.com/onnomarsman)
  //    input by: Robin
  //   example 1: is_nan(NaN)
  //   returns 1: true
  //   example 2: is_nan(0)
  //   returns 2: false

  let warningType = ''

  if (typeof val === 'number' && isNaN(val)) {
    return true
  }

  // Some errors for maximum PHP compatibility
  if (typeof val === 'object') {
    warningType = (Object.prototype.toString.call(val) === '[object Array]' ? 'array' : 'object')
  } else if (typeof val === 'string' && !val.match(/^[+-]?\d/)) {
    // simulate PHP's behaviour: '-9a' doesn't give a warning, but 'a9' does.
    warningType = 'string'
  }
  if (warningType) {
    throw new Error('Warning: is_nan() expects parameter 1 to be double, ' + warningType + ' given')
  }

  return false
}
