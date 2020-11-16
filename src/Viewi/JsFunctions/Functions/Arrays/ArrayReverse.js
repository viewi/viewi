function array_reverse (array, preserveKeys) { 
  var isArray = Object.prototype.toString.call(array) === '[object Array]'
  var tmpArr = preserveKeys ? {} : []
  var key
  if (isArray && !preserveKeys) {
    return array.slice(0).reverse()
  }
  if (preserveKeys) {
    var keys = []
    for (key in array) {
      keys.push(key)
    }
    var i = keys.length
    while (i--) {
      key = keys[i]
      tmpArr[key] = array[key]
    }
  } else {
    for (key in array) {
      tmpArr.unshift(array[key])
    }
  }
  return tmpArr
}
