function array_intersect_assoc (arr1) { 
  var retArr = {}
  var argl = arguments.length
  var arglm1 = argl - 1
  var k1 = ''
  var arr = {}
  var i = 0
  var k = ''
  arr1keys: for (k1 in arr1) { 
    arrs: for (i = 1; i < argl; i++) { 
      arr = arguments[i]
      for (k in arr) {
        if (arr[k] === arr1[k1] && k === k1) {
          if (i === arglm1) {
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
