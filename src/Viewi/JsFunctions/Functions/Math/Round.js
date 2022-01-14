function roundToInt (value, mode) {
  var tmp = Math.floor(Math.abs(value) + 0.5)
  if (
    (mode === 'PHP_ROUND_HALF_DOWN' && value === (tmp - 0.5)) ||
      (mode === 'PHP_ROUND_HALF_EVEN' && value === (0.5 + 2 * Math.floor(tmp / 2))) ||
      (mode === 'PHP_ROUND_HALF_ODD' && value === (0.5 + 2 * Math.floor(tmp / 2) - 1))) {
    tmp -= 1
  }
  return value < 0 ? -tmp : tmp
}
function round (value, precision = 0, mode = 'PHP_ROUND_HALF_UP') {
  var floatCast = window._php_cast_float
  var intCast = window._php_cast_int
  var p
  value = floatCast(value)
  precision = intCast(precision)
  p = Math.pow(10, precision)
  if (isNaN(value) || !isFinite(value)) {
    return value
  }
  if (Math.trunc(value) === value && precision >= 0) {
    return value
  }
  var preRoundPrecision = 14 - Math.floor(Math.log10(Math.abs(value)))
  if (preRoundPrecision > precision && preRoundPrecision - 15 < precision) {
    value = roundToInt(value * Math.pow(10, preRoundPrecision), mode)
    value /= Math.pow(10, Math.abs(precision - preRoundPrecision))
  } else {
    value *= p
  }
  value = roundToInt(value, mode)
  return value / p
}
