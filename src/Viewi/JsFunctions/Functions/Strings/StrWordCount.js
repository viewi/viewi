function str_word_count (str, format, charlist) { 
  var ctypeAlpha = window.ctype_alpha
  var len = str.length
  var cl = charlist && charlist.length
  var chr = ''
  var tmpStr = ''
  var i = 0
  var c = ''
  var wArr = []
  var wC = 0
  var assoc = {}
  var aC = 0
  var reg = ''
  var match = false
  var _pregQuote = function (str) {
    return (str + '').replace(/([\\.+*?[^\]$(){}=!<>|:])/g, '\\$1')
  }
  var _getWholeChar = function (str, i) {
    var code = str.charCodeAt(i)
    if (code < 0xD800 || code > 0xDFFF) {
      return str.charAt(i)
    }
    if (code >= 0xD800 && code <= 0xDBFF) {
      if (str.length <= (i + 1)) {
        throw new Error('High surrogate without following low surrogate')
      }
      var next = str.charCodeAt(i + 1)
      if (next < 0xDC00 || next > 0xDFFF) {
        throw new Error('High surrogate without following low surrogate')
      }
      return str.charAt(i) + str.charAt(i + 1)
    }
    if (i === 0) {
      throw new Error('Low surrogate without preceding high surrogate')
    }
    var prev = str.charCodeAt(i - 1)
    if (prev < 0xD800 || prev > 0xDBFF) {
      throw new Error('Low surrogate without preceding high surrogate')
    }
    return false
  }
  if (cl) {
    reg = '^(' + _pregQuote(_getWholeChar(charlist, 0))
    for (i = 1; i < cl; i++) {
      if ((chr = _getWholeChar(charlist, i)) === false) {
        continue
      }
      reg += '|' + _pregQuote(chr)
    }
    reg += ')$'
    reg = new RegExp(reg)
  }
  for (i = 0; i < len; i++) {
    if ((c = _getWholeChar(str, i)) === false) {
      continue
    }
    match = ctypeAlpha(c) ||
      (reg && c.search(reg) !== -1) ||
      ((i !== 0 && i !== len - 1) && c === '-') ||
      (i !== 0 && c === "'")
    if (match) {
      if (tmpStr === '' && format === 2) {
        aC = i
      }
      tmpStr = tmpStr + c
    }
    if (i === len - 1 || !match && tmpStr !== '') {
      if (format !== 2) {
        wArr[wArr.length] = tmpStr
      } else {
        assoc[aC] = tmpStr
      }
      tmpStr = ''
      wC++
    }
  }
  if (!format) {
    return wC
  } else if (format === 1) {
    return wArr
  } else if (format === 2) {
    return assoc
  }
  throw new Error('You have supplied an incorrect format')
}
