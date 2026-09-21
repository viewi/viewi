/**
 * A comparator for PHP's sort flags: SORT_REGULAR 0 (PHP 8 comparison), SORT_NUMERIC 1,
 * SORT_STRING 2, SORT_LOCALE_STRING 5, SORT_NATURAL 6, each optionally | SORT_FLAG_CASE 8.
 */
function _php_sort_compare(flags) {
  flags = flags || 0
  const caseFold = (flags & 8) !== 0
  const lower = function (s) {
    return caseFold ? s.toLowerCase() : s
  }
  const cmp = function (a, b) {
    return a < b ? -1 : (a > b ? 1 : 0)
  }
  switch (flags & ~8) {
    case 1:
      return function (a, b) {
        return cmp(_php_cast_float(a), _php_cast_float(b))
      }
    case 2:
      return function (a, b) {
        return cmp(lower(_phpCastString(a)), lower(_phpCastString(b)))
      }
    case 5:
      return function (a, b) {
        return _phpCastString(a).localeCompare(_phpCastString(b))
      }
    case 6:
      return function (a, b) {
        return caseFold ? strnatcasecmp(a, b) : strnatcmp(a, b)
      }
  }
  return _php_compare
}
