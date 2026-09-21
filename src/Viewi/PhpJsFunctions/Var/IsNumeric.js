function is_numeric(value) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.is-numeric.php
  // PHP 8: numbers, and strings that are a decimal number with optional sign, fraction and
  // exponent, surrounded by optional whitespace. No hex, no bare sign, no empty string.
  if (typeof value === 'number') {
    return true
  }
  return typeof value === 'string' &&
    /^[ \t\n\r\v\f]*[+-]?(\d+(\.\d*)?|\.\d+)([eE][+-]?\d+)?[ \t\n\r\v\f]*$/.test(value)
}
