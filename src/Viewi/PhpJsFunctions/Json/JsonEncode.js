function json_encode(value, flags) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.json-encode.php
  // PHP's output, not JSON.stringify's: '/' is escaped and non-ASCII becomes \uXXXX unless
  // JSON_UNESCAPED_SLASHES (64) / JSON_UNESCAPED_UNICODE (256); JSON_PRETTY_PRINT (128) indents
  // with 4 spaces; JSON_FORCE_OBJECT (16); JSON_HEX_TAG/AMP/APOS/QUOT (1/2/4/8).
  // Floats as PHP writes them (1.0e+25). NaN/INF can't be encoded: false, as PHP returns.
  flags = flags || 0
  const pretty = (flags & 128) !== 0
  const quote = function (str) {
    let out = '"'
    for (let i = 0; i < str.length; i++) {
      const ch = str[i]
      const code = str.charCodeAt(i)
      const hex = function (upper) {
        const h = ('000' + code.toString(16)).slice(-4)
        return '\\u' + (upper ? h.toUpperCase() : h)
      }
      if (ch === '"') {
        out += flags & 8 ? hex(true) : '\\"'
      } else if (ch === '\\') {
        out += '\\\\'
      } else if (ch === '/') {
        out += flags & 64 ? '/' : '\\/'
      } else if (ch === '\b') {
        out += '\\b'
      } else if (ch === '\f') {
        out += '\\f'
      } else if (ch === '\n') {
        out += '\\n'
      } else if (ch === '\r') {
        out += '\\r'
      } else if (ch === '\t') {
        out += '\\t'
      } else if (code < 0x20) {
        out += hex(false)
      } else if ((ch === '<' || ch === '>') && flags & 1) {
        out += hex(true)
      } else if (ch === '&' && flags & 2) {
        out += hex(true)
      } else if (ch === "'" && flags & 4) {
        out += hex(true)
      } else if (code > 0x7f && (!(flags & 256) || ((code === 0x2028 || code === 0x2029) && !(flags & 2048)))) {
        out += hex(false)
      } else {
        out += ch
      }
    }
    return out + '"'
  }
  const number = function (n) {
    if (Object.is(n, -0)) {
      return '-0'
    }
    if (Number.isSafeInteger(n)) {
      return String(n) // an integer to PHP too — microsecond timestamps must not turn into 1.7e+15
    }
    const parts = n.toExponential().split('e') // shortest round-trip digits, as serialize_precision -1
    const exponent = parseInt(parts[1], 10)
    if (exponent < -4 || exponent >= 15) {
      const mantissa = parts[0].indexOf('.') === -1 ? parts[0] + '.0' : parts[0]
      return mantissa + 'e' + (exponent < 0 ? '-' : '+') + Math.abs(exponent)
    }
    return String(n)
  }
  const encode = function (v, indent) {
    if (v === null || v === undefined) {
      return 'null'
    }
    switch (typeof v) {
      case 'boolean':
        return v ? 'true' : 'false'
      case 'number':
        if (!isFinite(v)) {
          throw new Error('Inf and NaN cannot be JSON encoded')
        }
        return number(v)
      case 'string':
        return quote(v)
      case 'object': {
        const isList = Array.isArray(v) && !(flags & 16)
        const keys = Object.keys(v)
        if (keys.length === 0) {
          return isList ? '[]' : '{}'
        }
        const inner = indent + '    '
        const items = keys.map(function (key) {
          const item = encode(v[key], inner)
          return isList ? item : quote(key) + (pretty ? ': ' : ':') + item
        })
        return pretty
          ? (isList ? '[' : '{') + '\n' + inner + items.join(',\n' + inner) + '\n' + indent + (isList ? ']' : '}')
          : (isList ? '[' : '{') + items.join(',') + (isList ? ']' : '}')
      }
    }
    return 'null'
  }
  try {
    return encode(value, '')
  } catch (e) {
    return false
  }
}
