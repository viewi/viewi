function array_replace_recursive (arr) { 
  var i = 0
  var p = ''
  var argl = arguments.length
  var retObj
  if (argl < 2) {
    throw new Error('There should be at least 2 arguments passed to array_replace_recursive()')
  }
  if (Object.prototype.toString.call(arr) === '[object Array]') {
    retObj = []
    for (p in arr) {
      retObj.push(arr[p])
    }
  } else {
    retObj = {}
    for (p in arr) {
      retObj[p] = arr[p]
    }
  }
  for (i = 1; i < argl; i++) {
    for (p in arguments[i]) {
      if (retObj[p] && typeof retObj[p] === 'object') {
        retObj[p] = array_replace_recursive(retObj[p], arguments[i][p])
      } else {
        retObj[p] = arguments[i][p]
      }
    }
  }
  return retObj
}
