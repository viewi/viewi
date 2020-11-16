function array_count_values (array) { 
  var tmpArr = {}
  var key = ''
  var t = ''
  var _getType = function (obj) {
    var t = typeof obj
    t = t.toLowerCase()
    if (t === 'object') {
      t = 'array'
    }
    return t
  }
  var _countValue = function (tmpArr, value) {
    if (typeof value === 'number') {
      if (Math.floor(value) !== value) {
        return
      }
    } else if (typeof value !== 'string') {
      return
    }
    if (value in tmpArr && tmpArr.hasOwnProperty(value)) {
      ++tmpArr[value]
    } else {
      tmpArr[value] = 1
    }
  }
  t = _getType(array)
  if (t === 'array') {
    for (key in array) {
      if (array.hasOwnProperty(key)) {
        _countValue.call(this, tmpArr, array[key])
      }
    }
  }
  return tmpArr
}
