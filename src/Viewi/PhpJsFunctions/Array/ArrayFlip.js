function array_flip(trans) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-flip.php
  // Only int and string values can become keys; PHP skips the rest with a warning.
  const pairs = []
  for (const [key, value] of _php_array_entries(trans)) {
    if (typeof value === 'string' || (typeof value === 'number' && Number.isInteger(value))) {
      pairs.push([value, key])
    }
  }
  return _php_array(pairs)
}
