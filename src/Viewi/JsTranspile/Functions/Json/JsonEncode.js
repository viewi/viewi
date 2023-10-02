function json_encode (mixedVal) { 
  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  var json = $global.JSON
  var retVal
  try {
    if (typeof json === 'object' && typeof json.stringify === 'function') {
      retVal = json.stringify(mixedVal)
      if (retVal === undefined) {
        throw new SyntaxError('json_encode')
      }
      return retVal
    }
    var value = mixedVal
    var quote = function (string) {
      var escapeChars = [
        '\u0000-\u001f',
        '\u007f-\u009f',
        '\u00ad',
        '\u0600-\u0604',
        '\u070f',
        '\u17b4',
        '\u17b5',
        '\u200c-\u200f',
        '\u2028-\u202f',
        '\u2060-\u206f',
        '\ufeff',
        '\ufff0-\uffff'
      ].join('')
      var escapable = new RegExp('[\\"' + escapeChars + ']', 'g')
      var meta = {
        '\b': '\\b',
        '\t': '\\t',
        '\n': '\\n',
        '\f': '\\f',
        '\r': '\\r',
        '"': '\\"',
        '\\': '\\\\'
      }
      escapable.lastIndex = 0
      return escapable.test(string) ? '"' + string.replace(escapable, function (a) {
        var c = meta[a]
        return typeof c === 'string' ? c : '\\u' + ('0000' + a.charCodeAt(0)
          .toString(16))
          .slice(-4)
      }) + '"' : '"' + string + '"'
    }
    var _str = function (key, holder) {
      var gap = ''
      var indent = '    '
      var i = 0
      var k = ''
      var v = ''
      var length = 0
      var mind = gap
      var partial = []
      var value = holder[key]
      if (value && typeof value === 'object' && typeof value.toJSON === 'function') {
        value = value.toJSON(key)
      }
      switch (typeof value) {
        case 'string':
          return quote(value)
        case 'number':
          return isFinite(value) ? String(value) : 'null'
        case 'boolean':
          return String(value)
        case 'object':
          if (!value) {
            return 'null'
          }
          gap += indent
          partial = []
          if (Object.prototype.toString.apply(value) === '[object Array]') {
            length = value.length
            for (i = 0; i < length; i += 1) {
              partial[i] = _str(i, value) || 'null'
            }
            v = partial.length === 0 ? '[]' : gap
              ? '[\n' + gap + partial.join(',\n' + gap) + '\n' + mind + ']'
              : '[' + partial.join(',') + ']'
            return v
          }
          for (k in value) {
            if (Object.hasOwnProperty.call(value, k)) {
              v = _str(k, value)
              if (v) {
                partial.push(quote(k) + (gap ? ': ' : ':') + v)
              }
            }
          }
          v = partial.length === 0 ? '{}' : gap
            ? '{\n' + gap + partial.join(',\n' + gap) + '\n' + mind + '}'
            : '{' + partial.join(',') + '}'
          return v
        case 'undefined':
        case 'function':
        default:
          throw new SyntaxError('json_encode')
      }
    }
    return _str('', {
      '': value
    })
  } catch (err) {
    if (!(err instanceof SyntaxError)) {
      throw new Error('Unexpected error type in json_encode()')
    }
    $locutus.php.last_error_json = 4
    return null
  }
}
