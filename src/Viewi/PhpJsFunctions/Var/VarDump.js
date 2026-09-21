function var_dump() { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.var-dump.php
  // Prints PHP's layout (int(1), string(3) "abc", array(1) {…}) and returns null, as PHP.
  // string(n) counts JS characters (see bytes-vs-chars); integral numbers are int (number-type).
  const pad = function (n) {
    return ' '.repeat(n)
  }
  const format = function (v, indent) {
    if (v === null || v === undefined) {
      return pad(indent) + 'NULL\n'
    }
    switch (typeof v) {
      case 'boolean':
        return pad(indent) + 'bool(' + v + ')\n'
      case 'number':
        return pad(indent) + (Number.isInteger(v) ? 'int(' + v + ')' : 'float(' + _phpCastString(v) + ')') + '\n'
      case 'string':
        return pad(indent) + 'string(' + v.length + ') "' + v + '"\n'
    }
    const entries = _php_array_entries(v)
    let out = pad(indent) + (is_object(v) ? 'object(' + v.constructor.name + ')#1 (' : 'array(') + entries.length + ') {\n'
    for (const [key, item] of entries) {
      out += pad(indent + 2) + '[' + (typeof key === 'number' ? key : '"' + key + '"') + ']=>\n' + format(item, indent + 2)
    }
    return out + pad(indent) + '}\n'
  }
  let text = ''
  for (let i = 0; i < arguments.length; i++) {
    text += format(arguments[i], 0)
  }
  echo(text.replace(/\n$/, ''))
  return null
}
