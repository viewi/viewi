function array_search(needle, haystack, argStrict) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.array-search.php
  // Loose mode follows PHP 8's ==; the key comes back as PHP holds it (5, not "5").
  const strict = !!argStrict
  for (const key in haystack) {
    if (Object.prototype.hasOwnProperty.call(haystack, key) &&
      (strict ? haystack[key] === needle : _php_compare(haystack[key], needle) === 0)) {
      return _php_array_key(key)
    }
  }
  return false
}
