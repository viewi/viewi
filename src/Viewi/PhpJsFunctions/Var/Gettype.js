function gettype(value) {
  //  discuss at: https://www.php.net/manual/en/function.gettype.php
  // PHP's names. A PHP array is a JS array or plain object → 'array'; class instances → 'object'.
  // JS can't tell 1 from 1.0: integral numbers are 'integer' (see number-type).
  if (value === null || value === undefined) {
    return 'NULL'
  }
  switch (typeof value) {
    case 'boolean':
      return 'boolean'
    case 'number':
      return Number.isInteger(value) ? 'integer' : 'double'
    case 'string':
      return 'string'
    case 'object':
      return is_object(value) ? 'object' : 'array'
  }
  return 'unknown type'
}
