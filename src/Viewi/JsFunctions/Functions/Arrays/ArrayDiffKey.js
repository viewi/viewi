function array_diff_key (arr1) { 
  var argl = arguments.length
  var retArr = {}
  var k1 = ''
  var i = 1
  var k = ''
  var arr = {}
  arr1keys: for (k1 in arr1) { 
    for (i = 1; i < argl; i++) {
      arr = arguments[i]
      for (k in arr) {
        if (k === k1) {
          continue arr1keys 
        }
      }
      retArr[k1] = arr1[k1]
    }
  }
  return retArr
}
