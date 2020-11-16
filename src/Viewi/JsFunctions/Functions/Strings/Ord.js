function ord (string) {
  var str = string + ''
  var code = str.charCodeAt(0)
  if (code >= 0xD800 && code <= 0xDBFF) {
    var hi = code
    if (str.length === 1) {
      return code
    }
    var low = str.charCodeAt(1)
    return ((hi - 0xD800) * 0x400) + (low - 0xDC00) + 0x10000
  }
  if (code >= 0xDC00 && code <= 0xDFFF) {
    return code
  }
  return code
}
