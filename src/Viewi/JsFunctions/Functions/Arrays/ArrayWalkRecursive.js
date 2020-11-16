function array_walk_recursive (array, funcname, userdata) { 
  if (!array || typeof array !== 'object') {
    return false
  }
  if (typeof funcname !== 'function') {
    return false
  }
  for (var key in array) {
    if (Object.prototype.toString.call(array[key]) === '[object Array]') {
      var funcArgs = [array[key], funcname]
      if (arguments.length > 2) {
        funcArgs.push(userdata)
      }
      if (array_walk_recursive.apply(null, funcArgs) === false) {
        return false
      }
      continue
    }
    try {
      if (arguments.length > 2) {
        funcname(array[key], key, userdata)
      } else {
        funcname(array[key], key)
      }
    } catch (e) {
      return false
    }
  }
  return true
}
