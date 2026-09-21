/**
 * Builds a PHP array from [key, value] pairs the way PHP would: keys normalised (true → 1,
 * null → "", 1.7 → 1, "5" → 5), a repeated key keeps its first position and takes the last value,
 * and the result is a JS array only when the keys are exactly 0..n-1 in order — otherwise an object.
 */
function _php_array(pairs) {
  const keyOf = function (key) {
    if (typeof key === 'number') {
      return Math.trunc(key)
    }
    if (typeof key === 'boolean') {
      return +key
    }
    if (key === null || key === undefined) {
      return ''
    }
    return _php_array_key(String(key))
  }
  const keys = []
  const values = new Map()
  for (const pair of pairs) {
    const key = keyOf(pair[0])
    if (!values.has(key)) {
      keys.push(key)
    }
    values.set(key, pair[1])
  }
  let isList = true
  for (let i = 0; i < keys.length; i++) {
    if (keys[i] !== i) {
      isList = false
      break
    }
  }
  if (isList) {
    return keys.map(function (key) {
      return values.get(key)
    })
  }
  const obj = {}
  for (const key of keys) {
    obj[key] = values.get(key)
  }
  return obj
}
