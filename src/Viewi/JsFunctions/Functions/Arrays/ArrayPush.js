function array_push (inputArr) { 
  var i = 0
  var pr = ''
  var argv = arguments
  var argc = argv.length
  var allDigits = /^\d$/
  var size = 0
  var highestIdx = 0
  var len = 0
  if (inputArr.hasOwnProperty('length')) {
    for (i = 1; i < argc; i++) {
      inputArr[inputArr.length] = argv[i]
    }
    return inputArr.length
  }
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
