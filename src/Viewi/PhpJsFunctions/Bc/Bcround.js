function bcround(num, precision) {
  //  discuss at: https://www.php.net/manual/en/function.bcround.php
  // Rounds half away from zero (PHP 8.4's default) to precision decimals; a negative precision
  // rounds to tens, hundreds…: bcround('1234.5678', -2) is '1200'. Exact, on BigInt.
  const match = _phpCastString(num).trim().match(/^([+-]?)(\d*)(?:\.(\d*))?$/)
  if (!match || (match[2] === '' && (match[3] || '') === '')) {
    throw new Error('bcround(): Argument #1 ($num) is not well-formed')
  }
  precision = _php_cast_int(precision || 0)
  const negative = match[1] === '-'
  const fraction = match[3] || ''
  let value = BigInt((match[2] || '0') + fraction)
  let scale = fraction.length
  if (precision < scale) {
    const divisor = 10n ** BigInt(scale - precision)
    let quotient = value / divisor
    if ((value % divisor) * 2n >= divisor) {
      quotient += 1n
    }
    value = precision < 0 ? quotient * 10n ** BigInt(-precision) : quotient
    scale = Math.max(precision, 0)
  } else {
    value = value * 10n ** BigInt(precision - scale)
    scale = precision
  }
  let digits = value.toString().padStart(scale + 1, '0')
  if (scale > 0) {
    digits = digits.slice(0, -scale) + '.' + digits.slice(-scale)
  }
  return (negative && value !== 0n ? '-' : '') + digits
}
