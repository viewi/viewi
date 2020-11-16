function array_change_key_case (array, cs) { 
  var caseFnc
  var key
  var tmpArr = {}
  if (Object.prototype.toString.call(array) === '[object Array]') {
    return array
  }
  if (array && typeof array === 'object') {
    caseFnc = (!cs || cs === 'CASE_LOWER') ? 'toLowerCase' : 'toUpperCase'
    for (key in array) {
      tmpArr[key[caseFnc]()] = array[key]
    }
    return tmpArr
  }
  return false
}
