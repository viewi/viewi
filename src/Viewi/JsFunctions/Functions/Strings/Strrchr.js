function strrchr (haystack, needle) {
  var pos = 0
  if (typeof needle !== 'string') {
    needle = String.fromCharCode(parseInt(needle, 10))
  }
  needle = needle.charAt(0)
  pos = haystack.lastIndexOf(needle)
  if (pos === -1) {
    return false
  }
  return haystack.substr(pos)
}
