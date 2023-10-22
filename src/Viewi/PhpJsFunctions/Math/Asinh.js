function asinh (arg) {
  //  discuss at: https://locutus.io/php/asinh/
  // original by: Onno Marsman (https://twitter.com/onnomarsman)
  //   example 1: asinh(8723321.4)
  //   returns 1: 16.67465779841863

  return Math.log(arg + Math.sqrt(arg * arg + 1))
}
