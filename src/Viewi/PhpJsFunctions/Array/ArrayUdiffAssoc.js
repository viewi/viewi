function array_udiff_assoc (arr1) { // eslint-disable-line camelcase
  //  discuss at: https://locutus.io/php/array_udiff_assoc/
  // original by: Brett Zamir (https://brett-zamir.me)
  //   example 1: array_udiff_assoc({0: 'kevin', 1: 'van', 2: 'Zonneveld'}, {0: 'Kevin', 4: 'van', 5: 'Zonneveld'}, function (f_string1, f_string2){var string1 = (f_string1+'').toLowerCase(); var string2 = (f_string2+'').toLowerCase(); if (string1 > string2) return 1; if (string1 === string2) return 0; return -1;})
  //   returns 1: {1: 'van', 2: 'Zonneveld'}

  const retArr = {}
  const arglm1 = arguments.length - 1
  let cb = arguments[arglm1]
  let arr = {}
  let i = 1
  let k1 = ''
  let k = ''

  const $global = (typeof window !== 'undefined' ? window : global)

  cb = (typeof cb === 'string')
    ? $global[cb]
    : (Object.prototype.toString.call(cb) === '[object Array]')
        ? $global[cb[0]][cb[1]]
        : cb

  arr1keys: for (k1 in arr1) { // eslint-disable-line no-labels
    for (i = 1; i < arglm1; i++) {
      arr = arguments[i]
      for (k in arr) {
        if (cb(arr[k], arr1[k1]) === 0 && k === k1) {
          // If it reaches here, it was found in at least one array, so try next value
          continue arr1keys // eslint-disable-line no-labels
        }
      }
      retArr[k1] = arr1[k1]
    }
  }

  return retArr
}
