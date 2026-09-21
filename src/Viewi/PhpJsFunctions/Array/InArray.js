function in_array(needle, haystack, argStrict) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.in-array.php
  // Loose mode compares the way PHP 8's == does (_php_compare): in_array(null, [0]) is true,
  // in_array('1e1', ['10']) is true, in_array('abc', [0]) is false.
  const strict = !!argStrict
  for (const key in haystack) {
    if (Object.prototype.hasOwnProperty.call(haystack, key) &&
      (strict ? haystack[key] === needle : _php_compare(haystack[key], needle) === 0)) {
      return true
    }
  }
  return false
}
