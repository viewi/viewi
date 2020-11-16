function array_values (input) { 
  var tmpArr = []
  var key = ''
  for (key in input) {
    tmpArr[tmpArr.length] = input[key]
  }
  return tmpArr
}
