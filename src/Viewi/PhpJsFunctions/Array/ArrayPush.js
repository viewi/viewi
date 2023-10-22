function array_push (inputArr) { // eslint-disable-line camelcase
  //  discuss at: https://locutus.io/php/array_push/
  // original by: Kevin van Zonneveld (https://kvz.io)
  // improved by: Brett Zamir (https://brett-zamir.me)
  //      note 1: Note also that IE retains information about property position even
  //      note 1: after being supposedly deleted, so if you delete properties and then
  //      note 1: add back properties with the same keys (including numeric) that had
  //      note 1: been deleted, the order will be as before; thus, this function is not
  //      note 1: really recommended with associative arrays (objects) in IE environments
  //   example 1: array_push(['kevin','van'], 'zonneveld')
  //   returns 1: 3

  let i = 0
  let pr = ''
  const argv = arguments
  const argc = argv.length
  const allDigits = /^\d$/
  let size = 0
  let highestIdx = 0
  let len = 0

  if (inputArr.hasOwnProperty('length')) {
    for (i = 1; i < argc; i++) {
      inputArr[inputArr.length] = argv[i]
    }
    return inputArr.length
  }

  // Associative (object)
  for (pr in inputArr) {
    if (inputArr.hasOwnProperty(pr)) {
      ++len
      if (pr.search(allDigits) !== -1) {
        size = parseInt(pr, 10)
        highestIdx = size > highestIdx ? size : highestIdx
      }
    }
  }
  for (i = 1; i < argc; i++) {
    inputArr[++highestIdx] = argv[i]
  }

  return len + i - 1
}
