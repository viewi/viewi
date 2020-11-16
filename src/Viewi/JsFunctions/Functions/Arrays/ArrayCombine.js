function array_combine (keys, values) { 
  var newArray = {}
  var i = 0
  if (typeof keys !== 'object') {
    return false
  }
  if (typeof values !== 'object') {
    return false
  }
  if (typeof keys.length !== 'number') {
    return false
  }
  if (typeof values.length !== 'number') {
    return false
  }
  if (!keys.length) {
    return false
  }
  if (keys.length !== values.length) {
    return false
  }
  for (i = 0; i < keys.length; i++) {
    newArray[keys[i]] = values[i]
  }
  return newArray
}
