function is_infinite (val) { // eslint-disable-line camelcase
  //  discuss at: https://locutus.io/php/is_infinite/
  // original by: Onno Marsman (https://twitter.com/onnomarsman)
  //   example 1: is_infinite(Infinity)
  //   returns 1: true
  //   example 2: is_infinite(-Infinity)
  //   returns 2: true
  //   example 3: is_infinite(0)
  //   returns 3: false

  let warningType = ''

  if (val === Infinity || val === -Infinity) {
    return true
  }

  // Some warnings for maximum PHP compatibility
  if (typeof val === 'object') {
    warningType = (Object.prototype.toString.call(val) === '[object Array]' ? 'array' : 'object')
  } else if (typeof val === 'string' && !val.match(/^[+-]?\d/)) {
    // simulate PHP's behaviour: '-9a' doesn't give a warning, but 'a9' does.
    warningType = 'string'
  }
  if (warningType) {
    const msg = 'Warning: is_infinite() expects parameter 1 to be double, ' + warningType + ' given'
    throw new Error(msg)
  }

  return false
}
