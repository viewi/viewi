function chop (str, charlist) {
  //  discuss at: https://locutus.io/php/chop/
  // original by: Paulo Freitas
  //   example 1: chop('    Kevin van Zonneveld    ')
  //   returns 1: '    Kevin van Zonneveld'


  return rtrim(str, charlist)
}
