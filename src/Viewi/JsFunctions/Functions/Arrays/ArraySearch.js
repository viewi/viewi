function array_search (needle, haystack, argStrict) { 
  var strict = !!argStrict
  var key = ''
  if (typeof needle === 'object' && needle.exec) {
    if (!strict) {
      var flags = 'i' + (needle.global ? 'g' : '') +
        (needle.multiline ? 'm' : '') +
        (needle.sticky ? 'y' : '')
      needle = new RegExp(needle.source, flags)
    }
    for (key in haystack) {
      if (haystack.hasOwnProperty(key)) {
        if (needle.test(haystack[key])) {
          return key
        }
      }
    }
    return false
  }
  for (key in haystack) {
    if (haystack.hasOwnProperty(key)) {
      if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) { 
        return key
      }
    }
  }
  return false
}
