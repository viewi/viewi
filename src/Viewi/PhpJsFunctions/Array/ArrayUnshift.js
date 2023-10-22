function array_unshift (array) { // eslint-disable-line camelcase
  //  discuss at: https://locutus.io/php/array_unshift/
  // original by: Kevin van Zonneveld (https://kvz.io)
  // improved by: Martijn Wieringa
  // improved by: jmweb
  //      note 1: Currently does not handle objects
  //   example 1: array_unshift(['van', 'Zonneveld'], 'Kevin')
  //   returns 1: 3

  let i = arguments.length

  while (--i !== 0) {
    arguments[0].unshift(arguments[i])
  }

  return arguments[0].length
}
