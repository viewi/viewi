function strptime (dateStr, format) {
  //  discuss at: https://locutus.io/php/strptime/
  // original by: Brett Zamir (https://brett-zamir.me)
  // original by: strftime
  //   example 1: strptime('20091112222135', '%Y%m%d%H%M%S') // Return value will depend on date and locale
  //   returns 1: {tm_sec: 35, tm_min: 21, tm_hour: 22, tm_mday: 12, tm_mon: 10, tm_year: 109, tm_wday: 4, tm_yday: 315, unparsed: ''}
  //   example 2: strptime('2009extra', '%Y')
  //   returns 2: {tm_sec:0, tm_min:0, tm_hour:0, tm_mday:0, tm_mon:0, tm_year:109, tm_wday:3, tm_yday: -1, unparsed: 'extra'}




  const retObj = {
    tm_sec: 0,
    tm_min: 0,
    tm_hour: 0,
    tm_mday: 0,
    tm_mon: 0,
    tm_year: 0,
    tm_wday: 0,
    tm_yday: 0,
    unparsed: ''
  }
  let i = 0
  let j = 0
  let amPmOffset = 0
  let prevHour = false
  const _reset = function (dateObj, realMday) {
    // realMday is to allow for a value of 0 in return results (but without
    // messing up the Date() object)
    let jan1
    const o = retObj
    const d = dateObj
    o.tm_sec = d.getUTCSeconds()
    o.tm_min = d.getUTCMinutes()
    o.tm_hour = d.getUTCHours()
    o.tm_mday = realMday === 0 ? realMday : d.getUTCDate()
    o.tm_mon = d.getUTCMonth()
    o.tm_year = d.getUTCFullYear() - 1900
    o.tm_wday = realMday === 0 ? (d.getUTCDay() > 0 ? d.getUTCDay() - 1 : 6) : d.getUTCDay()
    jan1 = new Date(Date.UTC(d.getUTCFullYear(), 0, 1))
    o.tm_yday = Math.ceil((d - jan1) / (1000 * 60 * 60 * 24))
  }
  const _date = function () {
    const o = retObj
    // We set date to at least 1 to ensure year or month doesn't go backwards
    return _reset(new Date(Date.UTC(
      o.tm_year + 1900,
      o.tm_mon,
      o.tm_mday || 1,
      o.tm_hour,
      o.tm_min,
      o.tm_sec
    )),
    o.tm_mday)
  }

  const _NWS = /\S/
  const _WS = /\s/

  const _aggregates = {
    c: 'locale',
    D: '%m/%d/%y',
    F: '%y-%m-%d',
    r: 'locale',
    R: '%H:%M',
    T: '%H:%M:%S',
    x: 'locale',
    X: 'locale'
  }

  /* Fix: Locale alternatives are supported though not documented in PHP; see https://linux.die.net/man/3/strptime
    Ec
    EC
    Ex
    EX
    Ey
    EY
    Od or Oe
    OH
    OI
    Om
    OM
    OS
    OU
    Ow
    OW
    Oy
  */
  const _pregQuote = function (str) {
    return (str + '').replace(/([\\.+*?[^\]$(){}=!<>|:])/g, '\\$1')
  }

  // ensure setup of localization variables takes place
  setlocale('LC_ALL', 0)

  const $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  const $locutus = $global.$locutus
  const locale = $locutus.php.localeCategories.LC_TIME
  const lcTime = $locutus.php.locales[locale].LC_TIME

  // First replace aggregates (run in a loop because an agg may be made up of other aggs)
  while (format.match(/%[cDFhnrRtTxX]/)) {
    format = format.replace(/%([cDFhnrRtTxX])/g, function (m0, m1) {
      const f = _aggregates[m1]
      return (f === 'locale' ? lcTime[m1] : f)
    })
  }

  const _addNext = function (j, regex, cb) {
    if (typeof regex === 'string') {
      regex = new RegExp('^' + regex, 'i')
    }
    const check = dateStr.slice(j)
    const match = regex.exec(check)
    // Even if the callback returns null after assigning to the
    // return object, the object won't be saved anyways
    const testNull = match ? cb.apply(null, match) : null
    if (testNull === null) {
      throw new Error('No match in string')
    }
    return j + match[0].length
  }

  const _addLocalized = function (j, formatChar, category) {
    // Could make each parenthesized instead and pass index to callback:
    return _addNext(j, arrayMap(_pregQuote, lcTime[formatChar]).join('|'),
      function (m) {
        const match = lcTime[formatChar].search(new RegExp('^' + _pregQuote(m) + '$', 'i'))
        if (match) {
          retObj[category] = match[0]
        }
      })
  }

  // BEGIN PROCESSING CHARACTERS
  for (i = 0, j = 0; i < format.length; i++) {
    if (format.charAt(i) === '%') {
      const literalPos = ['%', 'n', 't'].indexOf(format.charAt(i + 1))
      if (literalPos !== -1) {
        if (['%', '\n', '\t'].indexOf(dateStr.charAt(j)) === literalPos) {
          // a matched literal
          ++i
          // skip beyond
          ++j
          continue
        }
        // Format indicated a percent literal, but not actually present
        return false
      }
      var formatChar = format.charAt(i + 1)
      try {
        switch (formatChar) {
          case 'a':
          case 'A':
            // Sunday-Saturday
            // Changes nothing else
            j = _addLocalized(j, formatChar, 'tm_wday')
            break
          case 'h':
          case 'b':
            // Jan-Dec
            j = _addLocalized(j, 'b', 'tm_mon')
            // Also changes wday, yday
            _date()
            break
          case 'B':
            // January-December
            j = _addLocalized(j, formatChar, 'tm_mon')
            // Also changes wday, yday
            _date()
            break
          case 'C':
            // 0+; century (19 for 20th)
            // PHP docs say two-digit, but accepts one-digit (two-digit max):
            j = _addNext(j, /^\d?\d/,

              function (d) {
                const year = (parseInt(d, 10) - 19) * 100
                retObj.tm_year = year
                _date()
                if (!retObj.tm_yday) {
                  retObj.tm_yday = -1
                }
              // Also changes wday; and sets yday to -1 (always?)
              })
            break
          case 'd':
          case 'e':
            // 1-31 day
            j = _addNext(j, formatChar === 'd'
              ? /^(0[1-9]|[1-2]\d|3[0-1])/
              : /^([1-2]\d|3[0-1]|[1-9])/,
            function (d) {
              const dayMonth = parseInt(d, 10)
              retObj.tm_mday = dayMonth
              // Also changes w_day, y_day
              _date()
            })
            break
          case 'g':
            // No apparent effect; 2-digit year (see 'V')
            break
          case 'G':
            // No apparent effect; 4-digit year (see 'V')'
            break
          case 'H':
            // 00-23 hours
            j = _addNext(j, /^([0-1]\d|2[0-3])/, function (d) {
              const hour = parseInt(d, 10)
              retObj.tm_hour = hour
              // Changes nothing else
            })
            break
          case 'l':
          case 'I':
            // 01-12 hours
            j = _addNext(j, formatChar === 'l'
              ? /^([1-9]|1[0-2])/
              : /^(0[1-9]|1[0-2])/,
            function (d) {
              const hour = parseInt(d, 10) - 1 + amPmOffset
              retObj.tm_hour = hour
              // Used for coordinating with am-pm
              prevHour = true
              // Changes nothing else, but affected by prior 'p/P'
            })
            break
          case 'j':
            // 001-366 day of year
            j = _addNext(j, /^(00[1-9]|0[1-9]\d|[1-2]\d\d|3[0-6][0-6])/, function (d) {
              const dayYear = parseInt(d, 10) - 1
              retObj.tm_yday = dayYear
              // Changes nothing else
              // (oddly, since if original by a given year, could calculate other fields)
            })
            break
          case 'm':
            // 01-12 month
            j = _addNext(j, /^(0[1-9]|1[0-2])/, function (d) {
              const month = parseInt(d, 10) - 1
              retObj.tm_mon = month
              // Also sets wday and yday
              _date()
            })
            break
          case 'M':
            // 00-59 minutes
            j = _addNext(j, /^[0-5]\d/, function (d) {
              const minute = parseInt(d, 10)
              retObj.tm_min = minute
            // Changes nothing else
            })
            break
          case 'P':
            // Seems not to work; AM-PM
            // Could make fall-through instead since supposed to be a synonym despite PHP docs
            return false
          case 'p':
            // am-pm
            j = _addNext(j, /^(am|pm)/i, function (d) {
              // No effect on 'H' since already 24 hours but
              //   works before or after setting of l/I hour
              amPmOffset = /a/.test(d) ? 0 : 12
              if (prevHour) {
                retObj.tm_hour += amPmOffset
              }
            })
            break
          case 's':
            // Unix timestamp (in seconds)
            j = _addNext(j, /^\d+/, function (d) {
              const timestamp = parseInt(d, 10)
              const date = new Date(Date.UTC(timestamp * 1000))
              _reset(date)
              // Affects all fields, but can't be negative (and initial + not allowed)
            })
            break
          case 'S':
            // 00-59 seconds
            j = _addNext(j, /^[0-5]\d/, // strptime also accepts 60-61 for some reason

              function (d) {
                const second = parseInt(d, 10)
                retObj.tm_sec = second
              // Changes nothing else
              })
            break
          case 'u':
          case 'w':
            // 0 (Sunday)-6(Saturday)
            j = _addNext(j, /^\d/, function (d) {
              retObj.tm_wday = d - (formatChar === 'u')
              // Changes nothing else apparently
            })
            break
          case 'U':
          case 'V':
          case 'W':
            // Apparently ignored (week of year, from 1st Monday)
            break
          case 'y':
            // 69 (or higher) for 1969+, 68 (or lower) for 2068-
            // PHP docs say two-digit, but accepts one-digit (two-digit max):
            j = _addNext(j, /^\d?\d/,

              function (d) {
                d = parseInt(d, 10)
                const year = d >= 69 ? d : d + 100
                retObj.tm_year = year
                _date()
                if (!retObj.tm_yday) {
                  retObj.tm_yday = -1
                }
              // Also changes wday; and sets yday to -1 (always?)
              })
            break
          case 'Y':
            // 2010 (4-digit year)
            // PHP docs say four-digit, but accepts one-digit (four-digit max):
            j = _addNext(j, /^\d{1,4}/,

              function (d) {
                const year = (parseInt(d, 10)) - 1900
                retObj.tm_year = year
                _date()
                if (!retObj.tm_yday) {
                  retObj.tm_yday = -1
                }
              // Also changes wday; and sets yday to -1 (always?)
              })
            break
          case 'z':
            // Timezone; on my system, strftime gives -0800,
            // but strptime seems not to alter hour setting
            break
          case 'Z':
            // Timezone; on my system, strftime gives PST, but strptime treats text as unparsed
            break
          default:
            throw new Error('Unrecognized formatting character in strptime()')
        }
      } catch (e) {
        if (e === 'No match in string') {
          // Allow us to exit
          // There was supposed to be a matching format but there wasn't
          return false
        }
        // Calculate skipping beyond initial percent too
      }
      ++i
    } else if (format.charAt(i) !== dateStr.charAt(j)) {
      // If extra whitespace at beginning or end of either, or between formats, no problem
      // (just a problem when between % and format specifier)

      // If the string has white-space, it is ok to ignore
      if (dateStr.charAt(j).search(_WS) !== -1) {
        j++
        // Let the next iteration try again with the same format character
        i--
      } else if (format.charAt(i).search(_NWS) !== -1) {
        // Any extra formatting characters besides white-space causes
        // problems (do check after WS though, as may just be WS in string before next character)
        return false
      }
      // Extra WS in format
      // Adjust strings when encounter non-matching whitespace, so they align in future checks above
      // Will check on next iteration (against same (non-WS) string character)
    } else {
      j++
    }
  }

  // POST-PROCESSING
  // Will also get extra whitespace; empty string if none
  retObj.unparsed = dateStr.slice(j)
  return retObj
}
