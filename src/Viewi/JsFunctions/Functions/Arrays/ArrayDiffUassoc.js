function array_diff_uassoc (arr1) { 
  var retArr = {}
  var arglm1 = arguments.length - 1
  var cb = arguments[arglm1]
  var arr = {}
  var i = 1
  var k1 = ''
  var k = ''
  var $global = (typeof window !== 'undefined' ? window : global)
  cb = (typeof cb === 'string')
    ? $global[cb]
    : (Object.prototype.toString.call(cb) === '[object Array]')
      ? $global[cb[0]][cb[1]]
      : cb
  arr1keys: for (k1 in arr1) { 
    for (i = 1; i < arglm1; i++) {
      arr = arguments[i]
      for (k in arr) {
        if (arr[k] === arr1[k1] && cb(k, k1) === 0) {
          continue arr1keys 
        }
      }
      retArr[k1] = arr1[k1]
    }
  }
  return retArr
}
