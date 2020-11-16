function array_diff_assoc (arr1) { 
  var retArr = {}
  var argl = arguments.length
  var k1 = ''
  var i = 1
  var k = ''
  var arr = {}
  arr1keys: for (k1 in arr1) { 
    for (i = 1; i < argl; i++) {
      arr = arguments[i]
      for (k in arr) {
        if (arr[k] === arr1[k1] && k === k1) {
          continue arr1keys 
        }
      }
      retArr[k1] = arr1[k1]
    }
  }
  return retArr
}
