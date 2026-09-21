/**
 * A JS object key as PHP would hold it: PHP turns "5" into the integer 5, JS keeps every key a
 * string. For the functions that hand keys back (array_keys, array_search, array_flip…).
 */
function _php_array_key(key) {
  if (typeof key === 'string' && /^(0|-?[1-9]\d*)$/.test(key)) {
    const n = Number(key)
    if (Number.isSafeInteger(n)) {
      return n
    }
  }
  return key
}
