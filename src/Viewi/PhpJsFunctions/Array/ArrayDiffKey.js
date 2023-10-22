function array_diff_key (arr1) { // eslint-disable-line camelcase
  //  discuss at: https://locutus.io/php/array_diff_key/
  // original by: Ates Goral (https://magnetiq.com)
  //  revised by: Brett Zamir (https://brett-zamir.me)
  //    input by: Everlasto
  //   example 1: array_diff_key({red: 1, green: 2, blue: 3, white: 4}, {red: 5})
  //   returns 1: {"green":2, "blue":3, "white":4}
  //   example 2: array_diff_key({red: 1, green: 2, blue: 3, white: 4}, {red: 5}, {red: 5})
  //   returns 2: {"green":2, "blue":3, "white":4}

  const argl = arguments.length
  const retArr = {}
  let k1 = ''
  let i = 1
  let k = ''
  let arr = {}

  arr1keys: for (k1 in arr1) { // eslint-disable-line no-labels
    for (i = 1; i < argl; i++) {
      arr = arguments[i]
      for (k in arr) {
        if (k === k1) {
          // If it reaches here, it was found in at least one array, so try next value
          continue arr1keys // eslint-disable-line no-labels
        }
      }
      retArr[k1] = arr1[k1]
    }
  }

  return retArr
}
