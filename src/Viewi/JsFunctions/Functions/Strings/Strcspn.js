function strcspn (str, mask, start, length) {
  start = start || 0
  length = typeof length === 'undefined' ? str.length : (length || 0)
  if (start < 0) start = str.length + start
  if (length < 0) length = str.length - start + length
  if (start < 0 || start >= str.length || length <= 0 || e >= str.length) return 0
  var e = Math.min(str.length, start + length)
  for (var i = start, lgth = 0; i < e; i++) {
    if (mask.indexOf(str.charAt(i)) !== -1) {
      break
    }
    ++lgth
  }
  return lgth
}
