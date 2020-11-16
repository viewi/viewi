function array_walk (array, funcname, userdata) { 
  if (!array || typeof array !== 'object') {
    return false
  }
  try {
    if (typeof funcname === 'function') {
      for (var key in array) {
        if (arguments.length > 2) {
          funcname(array[key], key, userdata)
        } else {
          funcname(array[key], key)
        }
      }
    } else {
      return false
    }
  } catch (e) {
    return false
  }
  return true
}
