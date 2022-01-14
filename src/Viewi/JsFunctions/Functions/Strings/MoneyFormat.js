function money_format (format, number) { 
  var setlocale = window.setlocale
  if (typeof number !== 'number') {
    return null
  }
  var regex = /%((=.|[+^(!-])*?)(\d*?)(#(\d+))?(\.(\d+))?([in%])/g
  setlocale('LC_ALL', 0)
  var $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  var $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}
  var monetary = $locutus.php.locales[$locutus.php.localeCategories.LC_MONETARY].LC_MONETARY
  var doReplace = function (n0, flags, n2, width, n4, left, n6, right, conversion) {
    var value = ''
    var repl = ''
    if (conversion === '%') {
      return '%'
    }
    var fill = flags && (/=./).test(flags) ? flags.match(/=(.)/)[1] : ' ' 
    var showCurrSymbol = !flags || flags.indexOf('!') === -1
    width = parseInt(width, 10) || 0
    var neg = number < 0
    number = number + ''
    number = neg ? number.slice(1) : number
    var decpos = number.indexOf('.')
    var integer = decpos !== -1 ? number.slice(0, decpos) : number
    var fraction = decpos !== -1 ? number.slice(decpos + 1) : ''
    var _strSplice = function (integerStr, idx, thouSep) {
      var integerArr = integerStr.split('')
      integerArr.splice(idx, 0, thouSep)
      return integerArr.join('')
    }
    var intLen = integer.length
    left = parseInt(left, 10)
    var filler = intLen < left
    if (filler) {
      var fillnum = left - intLen
      integer = new Array(fillnum + 1).join(fill) + integer
    }
    if (flags.indexOf('^') === -1) {
      var thouSep = monetary.mon_thousands_sep
      var monGrouping = monetary.mon_grouping
      if (monGrouping[0] < integer.length) {
        for (var i = 0, idx = integer.length; i < monGrouping.length; i++) {
          idx -= monGrouping[i]
          if (idx <= 0) {
            break
          }
          if (filler && idx < fillnum) {
            thouSep = fill
          }
          integer = _strSplice(integer, idx, thouSep)
        }
      }
      if (monGrouping[i - 1] > 0) {
        while (idx > monGrouping[i - 1]) {
          idx -= monGrouping[i - 1]
          if (filler && idx < fillnum) {
            thouSep = fill
          }
          integer = _strSplice(integer, idx, thouSep)
        }
      }
    }
    if (right === '0') {
      value = integer
    } else {
      var decPt = monetary.mon_decimal_point
      if (right === '' || right === undefined) {
        right = conversion === 'i' ? monetary.int_frac_digits : monetary.frac_digits
      }
      right = parseInt(right, 10)
      if (right === 0) {
        fraction = ''
        decPt = ''
      } else if (right < fraction.length) {
        fraction = Math.round(parseFloat(
          fraction.slice(0, right) + '.' + fraction.substr(right, 1)
        ))
        if (right > fraction.length) {
          fraction = new Array(right - fraction.length + 1).join('0') + fraction 
        }
      } else if (right > fraction.length) {
        fraction += new Array(right - fraction.length + 1).join('0') 
      }
      value = integer + decPt + fraction
    }
    var symbol = ''
    if (showCurrSymbol) {
      symbol = conversion === 'i' ? monetary.int_curr_symbol : monetary.currency_symbol
    }
    var signPosn = neg ? monetary.n_sign_posn : monetary.p_sign_posn
    var sepBySpace = neg ? monetary.n_sep_by_space : monetary.p_sep_by_space
    var csPrecedes = neg ? monetary.n_cs_precedes : monetary.p_cs_precedes
    if (flags.indexOf('(') !== -1) {
      repl = (csPrecedes ? symbol + (sepBySpace === 1 ? ' ' : '') : '') + value + (!csPrecedes ? (
        sepBySpace === 1 ? ' ' : '') + symbol : '')
      if (neg) {
        repl = '(' + repl + ')'
      } else {
        repl = ' ' + repl + ' '
      }
    } else {
      var posSign = monetary.positive_sign
      var negSign = monetary.negative_sign
      var sign = neg ? (negSign) : (posSign)
      var otherSign = neg ? (posSign) : (negSign)
      var signPadding = ''
      if (signPosn) {
        signPadding = new Array(otherSign.length - sign.length + 1).join(' ')
      }
      var valueAndCS = ''
      switch (signPosn) {
        case 0:
          valueAndCS = csPrecedes
            ? symbol + (sepBySpace === 1 ? ' ' : '') + value
            : value + (sepBySpace === 1 ? ' ' : '') + symbol
          repl = '(' + valueAndCS + ')'
          break
        case 1:
          valueAndCS = csPrecedes
            ? symbol + (sepBySpace === 1 ? ' ' : '') + value
            : value + (sepBySpace === 1 ? ' ' : '') + symbol
          repl = signPadding + sign + (sepBySpace === 2 ? ' ' : '') + valueAndCS
          break
        case 2:
          valueAndCS = csPrecedes
            ? symbol + (sepBySpace === 1 ? ' ' : '') + value
            : value + (sepBySpace === 1 ? ' ' : '') + symbol
          repl = valueAndCS + (sepBySpace === 2 ? ' ' : '') + sign + signPadding
          break
        case 3:
          repl = csPrecedes
            ? signPadding + sign + (sepBySpace === 2 ? ' ' : '') + symbol +
              (sepBySpace === 1 ? ' ' : '') + value
            : value + (sepBySpace === 1 ? ' ' : '') + sign + signPadding +
              (sepBySpace === 2 ? ' ' : '') + symbol
          break
        case 4:
          repl = csPrecedes
            ? symbol + (sepBySpace === 2 ? ' ' : '') + signPadding + sign +
              (sepBySpace === 1 ? ' ' : '') + value
            : value + (sepBySpace === 1 ? ' ' : '') + symbol +
              (sepBySpace === 2 ? ' ' : '') + sign + signPadding
          break
      }
    }
    var padding = width - repl.length
    if (padding > 0) {
      padding = new Array(padding + 1).join(' ')
      if (flags.indexOf('-') !== -1) {
        repl += padding
      } else {
        repl = padding + repl
      }
    }
    return repl
  }
  return format.replace(regex, doReplace)
}
