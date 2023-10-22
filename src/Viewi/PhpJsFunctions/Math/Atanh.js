function atanh (arg) {
  //  discuss at: https://locutus.io/php/atanh/
  // original by: Onno Marsman (https://twitter.com/onnomarsman)
  //   example 1: atanh(0.3)
  //   returns 1: 0.3095196042031118

  return 0.5 * Math.log((1 + arg) / (1 - arg))
}
