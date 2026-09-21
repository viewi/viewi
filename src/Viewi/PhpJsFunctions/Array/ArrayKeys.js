function array_keys(input, searchValue, argStrict) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-keys.php
  // Keys come back as PHP holds them: integers for integer-like keys. With a search value,
  // loose mode follows PHP 8's ==.
  const search = searchValue !== undefined
  const strict = !!argStrict
  const keys = []
  for (const [key, value] of _php_array_entries(input)) {
    if (!search || (strict ? value === searchValue : _php_compare(value, searchValue) === 0)) {
      keys.push(key)
    }
  }
  return keys
}
