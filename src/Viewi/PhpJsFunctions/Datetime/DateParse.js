function date_parse(date) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.date-parse.php
  // The parts the string gives, via strtotime (read as UTC): hour/minute/second/fraction are
  // false when the string has no time, as PHP. Relative formats and zone details are not
  // reported. A string strtotime can't read gives one error with Viewi's own message: PHP's
  // parser reports per-position errors and warnings this port does not reproduce.
  let ts
  try {
    ts = strtotime(date)
  } catch (e) {
    ts = false
  }
  const hasTime = /\d{1,2}:\d{2}/.test(_phpCastString(date))
  const failed = ts === false || ts === null
  const dt = new Date((failed ? 0 : ts) * 1000)
  return {
    year: failed ? false : dt.getUTCFullYear(),
    month: failed ? false : dt.getUTCMonth() + 1,
    day: failed ? false : dt.getUTCDate(),
    hour: failed || !hasTime ? false : dt.getUTCHours(),
    minute: failed || !hasTime ? false : dt.getUTCMinutes(),
    second: failed || !hasTime ? false : dt.getUTCSeconds(),
    fraction: failed || !hasTime ? false : 0,
    warning_count: 0,
    warnings: [],
    error_count: failed ? 1 : 0,
    errors: failed ? { 0: 'The date could not be parsed' } : [],
    is_localtime: false
  }
}
