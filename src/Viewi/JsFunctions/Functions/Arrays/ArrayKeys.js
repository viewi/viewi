function array_keys (input, searchValue, argStrict) { 
  var search = typeof searchValue !== 'undefined'
  var tmpArr = []
  var strict = !!argStrict
  var include = true
  var key = ''
  for (key in input) {
    if (input.hasOwnProperty(key)) {
      include = true
      if (search) {
        if (strict && input[key] !== searchValue) {
          include = false
        } else if (input[key] !== searchValue) {
          include = false
        }
      }
      if (include) {
        tmpArr[tmpArr.length] = key
      }
    }
  }
  return tmpArr
}
