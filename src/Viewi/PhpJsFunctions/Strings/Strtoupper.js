function strtoupper (str) {
  //  discuss at: https://locutus.io/php/strtoupper/
  // original by: Kevin van Zonneveld (https://kvz.io)
  // improved by: Onno Marsman (https://twitter.com/onnomarsman)
  //   example 1: strtoupper('Kevin van Zonneveld')
  //   returns 1: 'KEVIN VAN ZONNEVELD'

  return (str + '')
    .toUpperCase()
}
