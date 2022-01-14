function substr (input, start, len) {
  var _php_cast_string = window._phpCastString
  input = _php_cast_string(input)
  var ini_get = window.ini_get
  var multibyte = ini_get('unicode.semantics') === 'on'
  if (multibyte) {
    input = input.match(/[\uD800-\uDBFF][\uDC00-\uDFFF]|[\s\S]/g) || []
  }
  var inputLength = input.length
  var end = inputLength
  if (start < 0) {
    start += end
  }
  if (typeof len !== 'undefined') {
    if (len < 0) {
      end = len + end
    } else {
      end = len + start
    }
  }
  if (start > inputLength || start < 0 || start > end) {
    return false
  }
  if (multibyte) {
    return input.slice(start, end).join('')
  }
  return input.slice(start, end)
}
