function array_unshift(arr) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-unshift.php
  // arr is by-reference: rewritten in place. Integer keys are renumbered, string keys kept.
  const pairs = []
  let next = 0
  for (let i = 1; i < arguments.length; i++) {
    pairs.push([next++, arguments[i]])
  }
  for (const [key, value] of _php_array_entries(arr)) {
    pairs.push([typeof key === 'number' ? next++ : key, value])
  }
  _php_array_set(arr, _php_array(pairs))
  return pairs.length
}
