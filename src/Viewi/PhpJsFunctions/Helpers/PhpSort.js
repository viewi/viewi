/**
 * The shared body of the sort functions, rewriting arr in place (by-reference). flags as in
 * _php_sort_compare. options: reverse, byKey (ksort),
 * keepKeys (asort family; int keys of a list then reorder, see int-key-order), compare (usort
 * family: the callback, whose result PHP casts to int). Stable, as PHP 8's sort is.
 */
function _php_sort(arr, flags, options) {
  const compare = options.compare
    ? function (a, b) {
      const r = options.compare(a, b)
      return typeof r === 'boolean' ? +r : (Math.trunc(Number(r)) || 0)
    }
    : _php_sort_compare(flags)
  const entries = _php_array_entries(arr)
  entries.sort(function (x, y) {
    const r = options.byKey ? compare(x[0], y[0]) : compare(x[1], y[1])
    return options.reverse ? -r : r
  })
  _php_array_set(arr, options.keepKeys
    ? _php_array(entries)
    : entries.map(function (entry) {
      return entry[1]
    }))
  return true
}
