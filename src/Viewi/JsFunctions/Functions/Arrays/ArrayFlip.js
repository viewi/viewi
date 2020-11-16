function array_flip (trans) { 
  var key
  var tmpArr = {}
  for (key in trans) {
    if (!trans.hasOwnProperty(key)) {
      continue
    }
    tmpArr[trans[key]] = key
  }
  return tmpArr
}
