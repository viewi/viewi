/**
 * A PHP array's [key, value] pairs in iteration order, keys as PHP holds them (int or string).
 * A list is a JS array; any other PHP array is a plain object - whose integer-like keys JS
 * always iterates first, in ascending order (see the int-key-order known difference).
 */
function _php_array_entries(arr) {
  if (Array.isArray(arr)) {
    return arr.map(function (value, index) {
      return [index, value]
    })
  }
  if (arr === null || typeof arr !== 'object') {
    return []
  }
  return Object.keys(arr).map(function (key) {
    return [_php_array_key(key), arr[key]]
  })
}
