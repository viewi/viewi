function substr_count (haystack, needle, offset, length) { 
  var cnt = 0
  haystack += ''
  needle += ''
  if (isNaN(offset)) {
    offset = 0
  }
  if (isNaN(length)) {
    length = 0
  }
  if (needle.length === 0) {
    return false
  }
  offset--
  while ((offset = haystack.indexOf(needle, offset + 1)) !== -1) {
    if (length > 0 && (offset + needle.length) > length) {
      return false
    }
    cnt++
  }
  return cnt
}
