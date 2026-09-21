function print_r(value, returnOutput) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.print-r.php
  // PHP's layout: nested arrays indent by 8 and end with a blank line; scalars print as PHP casts
  // them (true → '1', null → ''). Returns the text with returnOutput, else prints it and returns true.
  const pad = function (n) {
    return ' '.repeat(n)
  }
  const format = function (v, indent) {
    if (v === null || typeof v !== 'object') {
      return _phpCastString(v)
    }
    const head = is_object(v) ? v.constructor.name + ' Object' : 'Array'
    let out = head + '\n' + pad(indent) + '(\n'
    for (const [key, item] of _php_array_entries(v)) {
      out += pad(indent + 4) + '[' + key + '] => ' + format(item, indent + 8) + '\n'
    }
    return out + pad(indent) + ')\n'
  }
  const text = format(value, 0)
  if (returnOutput) {
    return text
  }
  echo(text)
  return true
}
