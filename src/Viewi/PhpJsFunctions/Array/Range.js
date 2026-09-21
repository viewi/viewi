function range(start, end, step) {
  //  discuss at: https://www.php.net/manual/en/function.range.php
  // PHP 8.3+: two one-character strings make a character range ('a'..'e', also '1'..'3'); other
  // numeric strings are numbers; floats anywhere give floats. A zero step, or a negative one for an
  // increasing range, throws; a decreasing range uses the step's absolute value.
  step = step === undefined ? 1 : step
  const isChar = function (v) {
    return typeof v === 'string' && v.length === 1
  }
  if (step === 0) {
    throw new Error('range(): Argument #3 ($step) cannot be 0')
  }
  const chars = isChar(start) && isChar(end) && Number.isInteger(step)
  const from = chars ? start.charCodeAt(0) : (typeof start === 'string' ? _php_cast_float(start) : Number(start))
  const to = chars ? end.charCodeAt(0) : (typeof end === 'string' ? _php_cast_float(end) : Number(end))
  if (from < to && step < 0) {
    throw new Error('range(): Argument #3 ($step) must be greater than 0 for increasing ranges')
  }
  step = Math.abs(step)
  const count = Math.floor(Math.abs(to - from) / step + 1e-9) + 1
  const direction = from <= to ? 1 : -1
  const out = []
  for (let i = 0; i < count; i++) {
    const value = from + direction * i * step
    out.push(chars ? String.fromCharCode(value) : value)
  }
  return out
}
