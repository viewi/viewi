function stripos (fHaystack, fNeedle, fOffset) {
  var haystack = (fHaystack + '').toLowerCase()
  var needle = (fNeedle + '').toLowerCase()
  var index = 0
  if ((index = haystack.indexOf(needle, fOffset)) !== -1) {
    return index
  }
  return false
}
