function sscanf (str, format) {
  var retArr = []
  var _NWS = /\S/
  var args = arguments
  var digit
  var _setExtraConversionSpecs = function (offset) {
    var matches = format.slice(offset).match(/%[cdeEufgosxX]/g)
    if (matches) {
      var lgth = matches.length
      while (lgth--) {
        retArr.push(null)
      }
    }
    return _finish()
  }
  var _finish = function () {
    if (args.length === 2) {
      return retArr
    }
    for (var i = 0; i < retArr.length; ++i) {
      args[i + 2].value = retArr[i]
    }
    return i
  }
  var _addNext = function (j, regex, cb) {
    if (assign) {
      var remaining = str.slice(j)
      var check = width ? remaining.substr(0, width) : remaining
      var match = regex.exec(check)
      var key = digit !== undefined
        ? digit
        : retArr.length
      var testNull = retArr[key] = match
          ? (cb
            ? cb.apply(null, match)
            : match[0])
          : null
      if (testNull === null) {
        throw new Error('No match in string')
      }
      return j + match[0].length
    }
    return j
  }
  if (arguments.length < 2) {
    throw new Error('Not enough arguments passed to sscanf')
  }
  for (var i = 0, j = 0; i < format.length; i++) {
    var width = 0
    var assign = true
    if (format.charAt(i) === '%') {
      if (format.charAt(i + 1) === '%') {
        if (str.charAt(j) === '%') {
          ++i
          ++j
          continue
        }
        return _setExtraConversionSpecs(i + 2)
      }
      var prePattern = new RegExp('^(?:(\\d+)\\$)?(\\*)?(\\d*)([hlL]?)', 'g')
      var preConvs = prePattern.exec(format.slice(i + 1))
      var tmpDigit = digit
      if (tmpDigit && preConvs[1] === undefined) {
        var msg = 'All groups in sscanf() must be expressed as numeric if '
        msg += 'any have already been used'
        throw new Error(msg)
      }
      digit = preConvs[1] ? parseInt(preConvs[1], 10) - 1 : undefined
      assign = !preConvs[2]
      width = parseInt(preConvs[3], 10)
      var sizeCode = preConvs[4]
      i += prePattern.lastIndex
      if (sizeCode) {
        switch (sizeCode) {
          case 'h':
          case 'l':
          case 'L':
            break
          default:
            throw new Error('Unexpected size specifier in sscanf()!')
        }
      }
      try {
        switch (format.charAt(i + 1)) {
          case 'F':
            break
          case 'g':
            break
          case 'G':
            break
          case 'b':
            break
          case 'i':
            var pattern = /([+-])?(?:(?:0x([\da-fA-F]+))|(?:0([0-7]+))|(\d+))/
            j = _addNext(j, pattern, function (num, sign, hex,
            oct, dec) {
              return hex ? parseInt(num, 16) : oct ? parseInt(num, 8) : parseInt(num, 10)
            })
            break
          case 'n':
            retArr[digit !== undefined ? digit : retArr.length - 1] = j
            break
          case 'c':
            j = _addNext(j, new RegExp('.{1,' + (width || 1) + '}'))
            break
          case 'D':
          case 'd':
            j = _addNext(j, /([+-])?(?:0*)(\d+)/, function (num, sign, dec) {
              var decInt = parseInt((sign || '') + dec, 10)
              if (decInt < 0) {
                return decInt < -2147483648 ? -2147483648 : decInt
              } else {
                return decInt < 2147483647 ? decInt : 2147483647
              }
            })
            break
          case 'f':
          case 'E':
          case 'e':
            j = _addNext(j, /([+-])?(?:0*)(\d*\.?\d*(?:[eE]?\d+)?)/, function (num, sign, dec) {
              if (dec === '.') {
                return null
              }
              return parseFloat((sign || '') + dec)
            })
            break
          case 'u':
            j = _addNext(j, /([+-])?(?:0*)(\d+)/, function (num, sign, dec) {
              var decInt = parseInt(dec, 10)
              if (sign === '-') {
                return 4294967296 - decInt
              } else {
                return decInt < 4294967295 ? decInt : 4294967295
              }
            })
            break
          case 'o':
            j = _addNext(j, /([+-])?(?:0([0-7]+))/, function (num, sign, oct) {
              return parseInt(num, 8)
            })
            break
          case 's':
            j = _addNext(j, /\S+/)
            break
          case 'X':
          case 'x':
            j = _addNext(j, /([+-])?(?:(?:0x)?([\da-fA-F]+))/, function (num, sign, hex) {
              return parseInt(num, 16)
            })
            break
          case '':
            throw new Error('Missing character after percent mark in sscanf() format argument')
          default:
            throw new Error('Unrecognized character after percent mark in sscanf() format argument')
        }
      } catch (e) {
        if (e === 'No match in string') {
          return _setExtraConversionSpecs(i + 2)
        }
      }
      ++i
    } else if (format.charAt(i) !== str.charAt(j)) {
      _NWS.lastIndex = 0
      if ((_NWS)
        .test(str.charAt(j)) || str.charAt(j) === '') {
        return _setExtraConversionSpecs(i + 1)
      } else {
        str = str.slice(0, j) + str.slice(j + 1)
        i--
      }
    } else {
      j++
    }
  }
  return _finish()
}
