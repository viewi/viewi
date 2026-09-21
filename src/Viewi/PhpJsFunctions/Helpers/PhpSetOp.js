/**
 * The shared body of the array_diff / array_intersect family. Keeps the entries of arrays[0]
 * found (intersect) or not found (diff) in the others, keys preserved. Values match as PHP
 * compares them here, by string cast ((string) $a === (string) $b), or by valueCompare; keys
 * match when compareKeys is set, as strings, or by keyCompare. Callbacks return 0 for "equal".
 */
function _php_set_op(arrays, intersect, compareValues, compareKeys, valueCompare, keyCompare) {
  const valueEqual = valueCompare
    ? function (a, b) {
      return Math.trunc(Number(valueCompare(a, b))) === 0
    }
    : function (a, b) {
      return _phpCastString(a) === _phpCastString(b)
    }
  const keyEqual = keyCompare
    ? function (a, b) {
      return Math.trunc(Number(keyCompare(a, b))) === 0
    }
    : function (a, b) {
      return String(a) === String(b)
    }
  const others = arrays.slice(1).map(_php_array_entries)
  const inOther = function (key, value) {
    return function (other) {
      return other.some(function (entry) {
        return (!compareValues || valueEqual(value, entry[1])) && (!compareKeys || keyEqual(key, entry[0]))
      })
    }
  }
  return _php_array(_php_array_entries(arrays[0]).filter(function (entry) {
    const found = inOther(entry[0], entry[1])
    return intersect ? others.every(found) : !others.some(found)
  }))
}
