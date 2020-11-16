function strripos (haystack, needle, offset) {
  haystack = (haystack + '')
    .toLowerCase()
  needle = (needle + '')
    .toLowerCase()
  var i = -1
  if (offset) {
    i = (haystack + '')
      .slice(offset)
      .lastIndexOf(needle) 
    if (i !== -1) {
      i += offset
    }
  } else {
    i = (haystack + '')
      .lastIndexOf(needle)
  }
  return i >= 0 ? i : false
}
