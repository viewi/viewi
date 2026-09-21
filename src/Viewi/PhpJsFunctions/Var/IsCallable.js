function is_callable(value) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.is-callable.php
  // A function, a [object, 'method'] pair, or the name of a global function. PHP function names
  // are not globals in the browser bundle, so is_callable('strlen') is false here.
  if (typeof value === 'function') {
    return true
  }
  if (typeof value === 'string') {
    return typeof globalThis[value] === 'function'
  }
  if (Array.isArray(value) && value.length === 2 && value[0] !== null && typeof value[0] === 'object') {
    return typeof value[0][value[1]] === 'function'
  }
  return false
}
