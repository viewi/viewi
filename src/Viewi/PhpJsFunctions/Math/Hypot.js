function hypot (x, y) {
  //  discuss at: https://locutus.io/php/hypot/
  // original by: Onno Marsman (https://twitter.com/onnomarsman)
  // imprived by: Robert Eisele (https://www.xarg.org/)
  //   example 1: hypot(3, 4)
  //   returns 1: 5
  //   example 2: hypot([], 'a')
  //   returns 2: null

  x = Math.abs(x)
  y = Math.abs(y)

  let t = Math.min(x, y)
  x = Math.max(x, y)
  t = t / x

  return x * Math.sqrt(1 + t * t) || null
}
