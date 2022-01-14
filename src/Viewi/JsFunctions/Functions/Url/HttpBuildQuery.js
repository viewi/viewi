function http_build_query (formdata, numericPrefix, argSeparator, encType) { 
  var encodeFunc
  switch (encType) {
    case 'PHP_QUERY_RFC3986':
      encodeFunc = window.rawurlencode
      break
    case 'PHP_QUERY_RFC1738':
    default:
      encodeFunc = window.urlencode
      break
  }
  var value
  var key
  var tmp = []
  var _httpBuildQueryHelper = function (key, val, argSeparator) {
    var k
    var tmp = []
    if (val === true) {
      val = '1'
    } else if (val === false) {
      val = '0'
    }
    if (val !== null) {
      if (typeof val === 'object') {
        for (k in val) {
          if (val[k] !== null) {
            tmp.push(_httpBuildQueryHelper(key + '[' + k + ']', val[k], argSeparator))
          }
        }
        return tmp.join(argSeparator)
      } else if (typeof val !== 'function') {
        return encodeFunc(key) + '=' + encodeFunc(val)
      } else {
        throw new Error('There was an error processing for http_build_query().')
      }
    } else {
      return ''
    }
  }
  if (!argSeparator) {
    argSeparator = '&'
  }
  for (key in formdata) {
    value = formdata[key]
    if (numericPrefix && !isNaN(key)) {
      key = String(numericPrefix) + key
    }
    var query = _httpBuildQueryHelper(key, value, argSeparator)
    if (query !== '') {
      tmp.push(query)
    }
  }
  return tmp.join(argSeparator)
}
