function array_replace (arr) { 
  var retObj = {}
  var i = 0
  var p = ''
  var argl = arguments.length
  if (argl < 2) {
    throw new Error('There should be at least 2 arguments passed to array_replace()')
  }
  for (p in arr) {
    retObj[p] = arr[p]
  }
  for (i = 1; i < argl; i++) {
    for (p in arguments[i]) {
      retObj[p] = arguments[i][p]
    }
  }
  return retObj
}
