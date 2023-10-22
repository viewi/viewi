function array_intersect_key (arr1) { // eslint-disable-line camelcase
  //  discuss at: https://locutus.io/php/array_intersect_key/
  // original by: Brett Zamir (https://brett-zamir.me)
  //      note 1: These only output associative arrays (would need to be
  //      note 1: all numeric and counting from zero to be numeric)
  //   example 1: var $array1 = {a: 'green', b: 'brown', c: 'blue', 0: 'red'}
  //   example 1: var $array2 = {a: 'green', 0: 'yellow', 1: 'red'}
  //   example 1: array_intersect_key($array1, $array2)
  //   returns 1: {0: 'red', a: 'green'}

  const retArr = {}
  const argl = arguments.length
  const arglm1 = argl - 1
  let k1 = ''
  let arr = {}
  let i = 0
  let k = ''

  arr1keys: for (k1 in arr1) { // eslint-disable-line no-labels
    if (!arr1.hasOwnProperty(k1)) {
      continue
    }
    arrs: for (i = 1; i < argl; i++) { // eslint-disable-line no-labels
      arr = arguments[i]
      for (k in arr) {
        if (!arr.hasOwnProperty(k)) {
          continue
        }
        if (k === k1) {
          if (i === arglm1) {
            retArr[k1] = arr1[k1]
          }
          // If the innermost loop always leads at least once to an equal value,
          // continue the loop until done
          continue arrs // eslint-disable-line no-labels
        }
      }
      // If it reaches here, it wasn't found in at least one array, so try next value
      continue arr1keys // eslint-disable-line no-labels
    }
  }

  return retArr
}
