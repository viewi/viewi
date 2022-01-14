function strptime (dateStr, format) {
  var setlocale = window.setlocale
  var arrayMap = window.array_map
  var retObj = {
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
  var i = 0
  var j = 0
  var amPmOffset = 0
  var prevHour = false
  var _reset = function (dateObj, realMday) {
    var jan1
    var o = retObj
    var d = dateObj
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
  var _date = function () {
    var o = retObj
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
  var _NWS = /\S/
  var _WS = /\s/
  var _aggregates = {
    c: 'locale',
    D: '%m/%d/%y',
    F: '%y-%m-%d',
    r: 'locale',
    R: '%H:%M',
    T: '%H:%M:%S',
    x: 'locale',
    X: 'locale'
  }
  var _pregQuote = function (str) {
    return (str + '').replace(/([\\.+*?[^\]$(){}=!<>|:])/g, '\\$1')
  }
  setlocale('LC_ALL', 0)
  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  var locale = $locutus.php.localeCategories.LC_TIME
  var lcTime = $locutus.php.locales[locale].LC_TIME
  while (format.match(/%[cDFhnrRtTxX]/)) {
    format = format.replace(/%([cDFhnrRtTxX])/g, function (m0, m1) {
      var f = _aggregates[m1]
      return (f === 'locale' ? lcTime[m1] : f)
    })
  }
  var _addNext = function (j, regex, cb) {
    if (typeof regex === 'string') {
      regex = new RegExp('^' + regex, 'i')
    }
    var check = dateStr.slice(j)
    var match = regex.exec(check)
    var testNull = match ? cb.apply(null, match) : null
    if (testNull === null) {
      throw new Error('No match in string')
    }
    return j + match[0].length
  }
  var _addLocalized = function (j, formatChar, category) {
    return _addNext(j, arrayMap(_pregQuote, lcTime[formatChar]).join('|'),
      function (m) {
        var match = lcTime[formatChar].search(new RegExp('^' + _pregQuote(m) + '$', 'i'))
        if (match) {
          retObj[category] = match[0]
        }
      })
  }
  for (i = 0, j = 0; i < format.length; i++) {
    if (format.charAt(i) === '%') {
      var literalPos = ['%', 'n', 't'].indexOf(format.charAt(i + 1))
      if (literalPos !== -1) {
        if (['%', '\n', '\t'].indexOf(dateStr.charAt(j)) === literalPos) {
          ++i
          ++j
          continue
        }
        return false
      }
      var formatChar = format.charAt(i + 1)
      try {
        switch (formatChar) {
          case 'a':
          case 'A':
            j = _addLocalized(j, formatChar, 'tm_wday')
            break
          case 'h':
          case 'b':
            j = _addLocalized(j, 'b', 'tm_mon')
            _date()
            break
          case 'B':
            j = _addLocalized(j, formatChar, 'tm_mon')
            _date()
            break
          case 'C':
            j = _addNext(j, /^\d?\d/,
            function (d) {
              var year = (parseInt(d, 10) - 19) * 100
              retObj.tm_year = year
              _date()
              if (!retObj.tm_yday) {
                retObj.tm_yday = -1
              }
            })
            break
          case 'd':
          case 'e':
            j = _addNext(j, formatChar === 'd'
              ? /^(0[1-9]|[1-2]\d|3[0-1])/
              : /^([1-2]\d|3[0-1]|[1-9])/,
            function (d) {
              var dayMonth = parseInt(d, 10)
              retObj.tm_mday = dayMonth
              _date()
            })
            break
          case 'g':
            break
          case 'G':
            break
          case 'H':
            j = _addNext(j, /^([0-1]\d|2[0-3])/, function (d) {
              var hour = parseInt(d, 10)
              retObj.tm_hour = hour
            })
            break
          case 'l':
          case 'I':
            j = _addNext(j, formatChar === 'l'
              ? /^([1-9]|1[0-2])/
              : /^(0[1-9]|1[0-2])/,
            function (d) {
              var hour = parseInt(d, 10) - 1 + amPmOffset
              retObj.tm_hour = hour
              prevHour = true
            })
            break
          case 'j':
            j = _addNext(j, /^(00[1-9]|0[1-9]\d|[1-2]\d\d|3[0-6][0-6])/, function (d) {
              var dayYear = parseInt(d, 10) - 1
              retObj.tm_yday = dayYear
            })
            break
          case 'm':
            j = _addNext(j, /^(0[1-9]|1[0-2])/, function (d) {
              var month = parseInt(d, 10) - 1
              retObj.tm_mon = month
              _date()
            })
            break
          case 'M':
            j = _addNext(j, /^[0-5]\d/, function (d) {
              var minute = parseInt(d, 10)
              retObj.tm_min = minute
            })
            break
          case 'P':
            return false
          case 'p':
            j = _addNext(j, /^(am|pm)/i, function (d) {
              amPmOffset = (/a/)
              .test(d) ? 0 : 12
              if (prevHour) {
                retObj.tm_hour += amPmOffset
              }
            })
            break
          case 's':
            j = _addNext(j, /^\d+/, function (d) {
              var timestamp = parseInt(d, 10)
              var date = new Date(Date.UTC(timestamp * 1000))
              _reset(date)
            })
            break
          case 'S':
            j = _addNext(j, /^[0-5]\d/, 
            function (d) {
              var second = parseInt(d, 10)
              retObj.tm_sec = second
            })
            break
          case 'u':
          case 'w':
            j = _addNext(j, /^\d/, function (d) {
              retObj.tm_wday = d - (formatChar === 'u')
            })
            break
          case 'U':
          case 'V':
          case 'W':
            break
          case 'y':
            j = _addNext(j, /^\d?\d/,
            function (d) {
              d = parseInt(d, 10)
              var year = d >= 69 ? d : d + 100
              retObj.tm_year = year
              _date()
              if (!retObj.tm_yday) {
                retObj.tm_yday = -1
              }
            })
            break
          case 'Y':
            j = _addNext(j, /^\d{1,4}/,
            function (d) {
              var year = (parseInt(d, 10)) - 1900
              retObj.tm_year = year
              _date()
              if (!retObj.tm_yday) {
                retObj.tm_yday = -1
              }
            })
            break
          case 'z':
            break
          case 'Z':
            break
          default:
            throw new Error('Unrecognized formatting character in strptime()')
        }
      } catch (e) {
        if (e === 'No match in string') {
          return false
        }
      }
      ++i
    } else if (format.charAt(i) !== dateStr.charAt(j)) {
      if (dateStr.charAt(j).search(_WS) !== -1) {
        j++
        i--
      } else if (format.charAt(i).search(_NWS) !== -1) {
        return false
      }
    } else {
      j++
    }
  }
  retObj.unparsed = dateStr.slice(j)
  return retObj
}
