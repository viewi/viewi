/**
 * Writes a by-reference result back into the caller's array (sort, array_splice, array_unshift…).
 * JS can't rebind the caller's variable, so the array/object is emptied and refilled in place —
 * which also means it can't change from object to array (see the by-ref-type-change known difference).
 */
function _php_array_set(target, value) {
  if (Array.isArray(target)) {
    target.length = 0
    if (Array.isArray(value)) {
      for (const item of value) {
        target.push(item)
      }
    } else {
      Object.assign(target, value)
    }
    return target
  }
  for (const key of Object.keys(target)) {
    delete target[key]
  }
  Object.assign(target, value)
  return target
}
