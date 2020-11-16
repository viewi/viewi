function array_diff_ukey (arr1) { 
  var retArr = {}
  var arglm1 = arguments.length - 1
  var cb = arguments[arglm1]
  var k1 = ''
  var i = 1
  var arr = {}
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
        if (cb(k, k1) === 0) {
          continue arr1keys 
        }
      }
      retArr[k1] = arr1[k1]
    }
  }
  return retArr
}
