function _php_cast_float(value) { // eslint-disable-line camelcase
  // PHP's (float): strings take their leading numeric part ("1.5abc" → 1.5, "abc" → 0),
  // booleans and arrays go through (int).
  if (typeof value === 'number') {
    return value
  }
  if (typeof value === 'string') {
    const prefix = value.match(/^[ \t\n\r\v\f]*[+-]?(\d+(\.\d*)?|\.\d+)([eE][+-]?\d+)?/)
    return prefix ? Number(prefix[0].trim()) : 0
  }
  return _php_cast_int(value)
}
