function array_uintersect (arr1) { 
  var retArr = {}
  var arglm1 = arguments.length - 1
  var arglm2 = arglm1 - 1
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
    arrs: for (i = 1; i < arglm1; i++) { 
      arr = arguments[i]
      for (k in arr) {
        if (cb(arr[k], arr1[k1]) === 0) {
          if (i === arglm2) {
            retArr[k1] = arr1[k1]
          }
          continue arrs 
        }
      }
      continue arr1keys 
    }
  }
  return retArr
}
