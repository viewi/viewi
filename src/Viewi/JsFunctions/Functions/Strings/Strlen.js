function strlen (string) {
  var str = string + ''
  var iniVal = (typeof require !== 'undefined' ? require('../info/ini_get')('unicode.semantics') : undefined) || 'off'
  if (iniVal === 'off') {
    return str.length
  }
  var i = 0
  var lgth = 0
  var getWholeChar = function (str, i) {
    var code = str.charCodeAt(i)
    var next = ''
    var prev = ''
    if (code >= 0xD800 && code <= 0xDBFF) {
      if (str.length <= (i + 1)) {
        throw new Error('High surrogate without following low surrogate')
      }
      next = str.charCodeAt(i + 1)
      if (next < 0xDC00 || next > 0xDFFF) {
        throw new Error('High surrogate without following low surrogate')
      }
      return str.charAt(i) + str.charAt(i + 1)
    } else if (code >= 0xDC00 && code <= 0xDFFF) {
      if (i === 0) {
        throw new Error('Low surrogate without preceding high surrogate')
      }
      prev = str.charCodeAt(i - 1)
      if (prev < 0xD800 || prev > 0xDBFF) {
        throw new Error('Low surrogate without preceding high surrogate')
      }
      return false
    }
    return str.charAt(i)
  }
  for (i = 0, lgth = 0; i < str.length; i++) {
    if ((getWholeChar(str, i)) === false) {
      continue
    }
    lgth++
  }
  return lgth
}
