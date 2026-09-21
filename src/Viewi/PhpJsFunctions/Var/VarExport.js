function var_export(value, returnOutput) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.var-export.php
  // PHP's layout: 'array (' blocks indented by 2, a nested array starts on the next line, NULL in
  // capitals, strings single-quoted. Integral numbers print as integers (see number-type).
  // Returns the text with returnOutput, else prints it and returns null.
  const pad = function (n) {
    return ' '.repeat(n)
  }
  const number = function (n) {
    if (Number.isSafeInteger(n)) {
      return String(n)
    }
    if (!isFinite(n)) {
      return isNaN(n) ? 'NAN' : (n > 0 ? 'INF' : '-INF')
    }
    const parts = n.toExponential().split('e')
    const exponent = parseInt(parts[1], 10)
    if (exponent < -4 || exponent >= 15) {
      return (parts[0].indexOf('.') === -1 ? parts[0] + '.0' : parts[0]) + 'E' + (exponent < 0 ? '-' : '+') + Math.abs(exponent)
    }
    const s = String(n)
    return s.indexOf('.') === -1 ? s + '.0' : s
  }
  const format = function (v, indent) {
    if (v === null || v === undefined) {
      return 'NULL'
    }
    switch (typeof v) {
      case 'boolean':
        return v ? 'true' : 'false'
      case 'number':
        return number(v)
      case 'string':
        return "'" + v.replace(/\\/g, '\\\\').replace(/'/g, "\\'") + "'"
    }
    let out = (is_object(v) ? '(object) array(' : 'array (') + '\n'
    for (const [key, item] of _php_array_entries(v)) {
      const k = typeof key === 'number' ? String(key) : "'" + key.replace(/\\/g, '\\\\').replace(/'/g, "\\'") + "'"
      const nested = item !== null && typeof item === 'object'
      out += pad(indent + 2) + k + ' => ' + (nested ? '\n' + pad(indent + 2) : '') + format(item, indent + 2) + ',\n'
    }
    return out + pad(indent) + ')'
  }
  const text = format(value, 0)
  if (returnOutput) {
    return text
  }
  echo(text)
  return null
}
