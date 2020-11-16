function in_array (needle, haystack, argStrict) { 
  var key = ''
  var strict = !!argStrict
  if (strict) {
    for (key in haystack) {
      if (haystack[key] === needle) {
        return true
      }
    }
  } else {
    for (key in haystack) {
      if (haystack[key] == needle) { 
        return true
      }
    }
  }
  return false
}
