function array_fill_keys (keys, value) { 
  var retObj = {}
  var key = ''
  for (key in keys) {
    retObj[keys[key]] = value
  }
  return retObj
}
