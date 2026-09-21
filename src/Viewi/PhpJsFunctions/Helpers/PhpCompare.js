/**
 * PHP 8's comparison, as <=> / == / < see it: -1, 0 or 1.
 * - two numeric strings compare as numbers ("10" > "9"); other strings compare as text
 * - number vs numeric string: as numbers; number vs other string: the number as a string
 * - null vs string: "" vs the string; null/bool vs anything else: both as booleans
 * - arrays: fewer elements is smaller, then element by element; an array beats any scalar
 */
function _php_compare(a, b) {
  const numeric = function (s) {
    return /^[ \t\n\r\v\f]*[+-]?(\d+(\.\d*)?|\.\d+)([eE][+-]?\d+)?[ \t\n\r\v\f]*$/.test(s)
  }
  const cmp = function (x, y) {
    return x < y ? -1 : (x > y ? 1 : 0)
  }
  const kind = function (v) {
    if (v === null || v === undefined) {
      return 'null'
    }
    return typeof v === 'object' ? 'array' : typeof v
  }
  const toBool = function (v) {
    if (v === null || v === undefined) {
      return false
    }
    if (typeof v === 'object') {
      return Object.keys(v).length > 0
    }
    if (typeof v === 'string') {
      return v !== '' && v !== '0'
    }
    return !!v
  }
  const ka = kind(a)
  const kb = kind(b)
  if (ka === 'string' && kb === 'string') {
    return numeric(a) && numeric(b) ? cmp(+a, +b) : cmp(a, b)
  }
  if (ka === 'null' && kb === 'string') {
    return cmp('', b)
  }
  if (ka === 'string' && kb === 'null') {
    return cmp(a, '')
  }
  if (ka === 'null' || kb === 'null' || ka === 'boolean' || kb === 'boolean') {
    return cmp(toBool(a), toBool(b))
  }
  if (ka === 'number' && kb === 'number') {
    return cmp(a, b)
  }
  if (ka === 'number' && kb === 'string') {
    return numeric(b) ? cmp(a, +b) : cmp(_phpCastString(a), b)
  }
  if (ka === 'string' && kb === 'number') {
    return numeric(a) ? cmp(+a, b) : cmp(a, _phpCastString(b))
  }
  if (ka === 'array' && kb === 'array') {
    const keys = Object.keys(a)
    const count = cmp(keys.length, Object.keys(b).length)
    if (count !== 0) {
      return count
    }
    for (const key of keys) {
      if (!Object.prototype.hasOwnProperty.call(b, key)) {
        return 1 // uncomparable: PHP answers 1
      }
      const item = _php_compare(a[key], b[key])
      if (item !== 0) {
        return item
      }
    }
    return 0
  }
  return ka === 'array' ? 1 : -1
}
