function lcg_value () { // eslint-disable-line camelcase
  //  discuss at: https://locutus.io/php/lcg_value/
  // original by: Onno Marsman (https://twitter.com/onnomarsman)
  //   example 1: var $rnd = lcg_value()
  //   example 1: var $result = $rnd >= 0 && $rnd <= 1
  //   returns 1: true

  return Math.random()
}
