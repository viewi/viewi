function _php_cast_int(value) { // eslint-disable-line camelcase
  // PHP's (int): strings take their leading numeric part, exponent included ("1e3" → 1000,
  // " 42abc" → 42, "abc" → 0); floats truncate toward zero; NaN/INF → 0; arrays → 0 or 1.
  switch (typeof value) {
    case 'number':
      return Number.isFinite(value) ? Math.trunc(value) : 0
    case 'string': {
      const prefix = value.match(/^[ \t\n\r\v\f]*[+-]?(\d+(\.\d*)?|\.\d+)([eE][+-]?\d+)?/)
      if (!prefix) {
        return 0
      }
      const n = Number(prefix[0].trim())
      return Number.isFinite(n) ? Math.trunc(n) : 0
    }
    case 'boolean':
      return +value
    case 'object':
      return value === null ? 0 : (Object.keys(value).length > 0 ? 1 : 0)
  }
  return 0
}
