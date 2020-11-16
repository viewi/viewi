function array_udiff_uassoc (arr1) { 
  var retArr = {}
  var arglm1 = arguments.length - 1
  var arglm2 = arglm1 - 1
  var cb = arguments[arglm1]
  var cb0 = arguments[arglm2]
  var k1 = ''
  var i = 1
  var k = ''
  var arr = {}
  var $global = (typeof window !== 'undefined' ? window : global)
  cb = (typeof cb === 'string')
    ? $global[cb]
    : (Object.prototype.toString.call(cb) === '[object Array]')
      ? $global[cb[0]][cb[1]]
      : cb
  cb0 = (typeof cb0 === 'string')
    ? $global[cb0]
    : (Object.prototype.toString.call(cb0) === '[object Array]')
      ? $global[cb0[0]][cb0[1]]
      : cb0
  arr1keys: for (k1 in arr1) { 
    for (i = 1; i < arglm2; i++) {
      arr = arguments[i]
      for (k in arr) {
        if (cb0(arr[k], arr1[k1]) === 0 && cb(k, k1) === 0) {
          continue arr1keys 
        }
      }
      retArr[k1] = arr1[k1]
    }
  }
  return retArr
}
