function array_map (callback) { 
  var argc = arguments.length
  var argv = arguments
  var obj = null
  var cb = callback
  var j = argv[1].length
  var i = 0
  var k = 1
  var m = 0
  var tmp = []
  var tmpArr = []
  var $global = (typeof window !== 'undefined' ? window : global)
  while (i < j) {
    while (k < argc) {
      tmp[m++] = argv[k++][i]
    }
    m = 0
    k = 1
    if (callback) {
      if (typeof callback === 'string') {
        cb = $global[callback]
      } else if (typeof callback === 'object' && callback.length) {
        obj = typeof callback[0] === 'string' ? $global[callback[0]] : callback[0]
        if (typeof obj === 'undefined') {
          throw new Error('Object not found: ' + callback[0])
        }
        cb = typeof callback[1] === 'string' ? obj[callback[1]] : callback[1]
      }
      tmpArr[i++] = cb.apply(obj, tmp)
    } else {
      tmpArr[i++] = tmp
    }
    tmp = []
  }
  return tmpArr
}
