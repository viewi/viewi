function str_pad(input, length, padString, padType) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.str-pad.php
  // padType is PHP's constant value: STR_PAD_LEFT 0, STR_PAD_RIGHT 1 (default), STR_PAD_BOTH 2.
  // STR_PAD_BOTH puts the smaller half on the left: str_pad('x', 6, 'ab', STR_PAD_BOTH) is 'abxaba'.
  input = _phpCastString(input)
  padString = padString === undefined ? ' ' : _phpCastString(padString)
  const total = Math.trunc(length) - input.length
  if (total <= 0 || padString === '') {
    return input
  }
  const repeat = function (n) {
    return padString.repeat(Math.ceil(n / padString.length)).slice(0, n)
  }
  if (padType === 0) {
    return repeat(total) + input
  }
  if (padType === 2) {
    const left = Math.floor(total / 2)
    return repeat(left) + input + repeat(total - left)
  }
  return input + repeat(total)
}
