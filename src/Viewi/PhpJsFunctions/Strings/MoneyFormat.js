function money_format (format, number) { // eslint-disable-line camelcase
  //  discuss at: https://locutus.io/php/money_format/
  // original by: Brett Zamir (https://brett-zamir.me)
  //    input by: daniel airton wermann (https://wermann.com.br)
  // bugfixed by: Brett Zamir (https://brett-zamir.me)
  //      note 1: This depends on setlocale having the appropriate
  //      note 1: locale (these examples use 'en_US')
  //   example 1: money_format('%i', 1234.56)
  //   returns 1: ' USD 1,234.56'
  //   example 2: money_format('%14#8.2n', 1234.5678)
  //   returns 2: ' $     1,234.57'
  //   example 3: money_format('%14#8.2n', -1234.5678)
  //   returns 3: '-$     1,234.57'
  //   example 4: money_format('%(14#8.2n', 1234.5678)
  //   returns 4: ' $     1,234.57 '
  //   example 5: money_format('%(14#8.2n', -1234.5678)
  //   returns 5: '($     1,234.57)'
  //   example 6: money_format('%=014#8.2n', 1234.5678)
  //   returns 6: ' $000001,234.57'
  //   example 7: money_format('%=014#8.2n', -1234.5678)
  //   returns 7: '-$000001,234.57'
  //   example 8: money_format('%=*14#8.2n', 1234.5678)
  //   returns 8: ' $*****1,234.57'
  //   example 9: money_format('%=*14#8.2n', -1234.5678)
  //   returns 9: '-$*****1,234.57'
  //  example 10: money_format('%=*^14#8.2n', 1234.5678)
  //  returns 10: '  $****1234.57'
  //  example 11: money_format('%=*^14#8.2n', -1234.5678)
  //  returns 11: ' -$****1234.57'
  //  example 12: money_format('%=*!14#8.2n', 1234.5678)
  //  returns 12: ' *****1,234.57'
  //  example 13: money_format('%=*!14#8.2n', -1234.5678)
  //  returns 13: '-*****1,234.57'
  //  example 14: money_format('%i', 3590)
  //  returns 14: ' USD 3,590.00'



  // Per PHP behavior, there seems to be no extra padding
  // for sign when there is a positive number, though my
  // understanding of the description is that there should be padding;
  // need to revisit examples

  // Helpful info at https://ftp.gnu.org/pub/pub/old-gnu/Manuals/glibc-2.2.3/html_chapter/libc_7.html
  // and https://publib.boulder.ibm.com/infocenter/zos/v1r10/index.jsp?topic=/com.ibm.zos.r10.bpxbd00/strfmp.htm

  if (typeof number !== 'number') {
    return null
  }
  // 1: flags, 3: width, 5: left, 7: right, 8: conversion
  const regex = /%((=.|[+^(!-])*?)(\d*?)(#(\d+))?(\.(\d+))?([in%])/g

  // Ensure the locale data we need is set up
  setlocale('LC_ALL', 0)

  const $global = (typeof window !== 'undefined' ? window : global)
  $global.$locutus = $global.$locutus || {}
  const $locutus = $global.$locutus
  $locutus.php = $locutus.php || {}

  const monetary = $locutus.php.locales[$locutus.php.localeCategories.LC_MONETARY].LC_MONETARY

  const doReplace = function (n0, flags, n2, width, n4, left, n6, right, conversion) {
    let value = ''
    let repl = ''
    if (conversion === '%') {
      // Percent does not seem to be allowed with intervening content
      return '%'
    }
    const fill = flags && (/=./).test(flags) ? flags.match(/=(.)/)[1] : ' ' // flag: =f (numeric fill)
    // flag: ! (suppress currency symbol)
    const showCurrSymbol = !flags || flags.indexOf('!') === -1
    // field width: w (minimum field width)
    width = parseInt(width, 10) || 0

    const neg = number < 0
    // Convert to string
    number = number + ''
    // We don't want negative symbol represented here yet
    number = neg ? number.slice(1) : number

    const decpos = number.indexOf('.')
    // Get integer portion
    let integer = decpos !== -1 ? number.slice(0, decpos) : number
    // Get decimal portion
    let fraction = decpos !== -1 ? number.slice(decpos + 1) : ''

    const _strSplice = function (integerStr, idx, thouSep) {
      const integerArr = integerStr.split('')
      integerArr.splice(idx, 0, thouSep)
      return integerArr.join('')
    }

    const intLen = integer.length
    left = parseInt(left, 10)
    const filler = intLen < left
    if (filler) {
      var fillnum = left - intLen
      integer = new Array(fillnum + 1).join(fill) + integer
    }
    if (flags.indexOf('^') === -1) {
      // flag: ^ (disable grouping characters (of locale))
      // use grouping characters
      // ','
      let thouSep = monetary.mon_thousands_sep
      // [3] (every 3 digits in U.S.A. locale)
      const monGrouping = monetary.mon_grouping

      if (monGrouping[0] < integer.length) {
        for (var i = 0, idx = integer.length; i < monGrouping.length; i++) {
          // e.g., 3
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
        // Repeating last grouping (may only be one) until highest portion of integer reached
        while (idx > monGrouping[i - 1]) {
          idx -= monGrouping[i - 1]
          if (filler && idx < fillnum) {
            thouSep = fill
          }
          integer = _strSplice(integer, idx, thouSep)
        }
      }
    }

    // left, right
    if (right === '0') {
      // No decimal or fractional digits
      value = integer
    } else {
      // '.'
      let decPt = monetary.mon_decimal_point
      if (right === '' || right === undefined) {
        right = conversion === 'i' ? monetary.int_frac_digits : monetary.frac_digits
      }
      right = parseInt(right, 10)

      if (right === 0) {
        // Only remove fractional portion if explicitly set to zero digits
        fraction = ''
        decPt = ''
      } else if (right < fraction.length) {
        fraction = Math.round(parseFloat(
          fraction.slice(0, right) + '.' + fraction.substr(right, 1)
        ))
        if (right > fraction.length) {
          fraction = new Array(right - fraction.length + 1).join('0') + fraction // prepend with 0's
        }
      } else if (right > fraction.length) {
        fraction += new Array(right - fraction.length + 1).join('0') // pad with 0's
      }
      value = integer + decPt + fraction
    }

    let symbol = ''
    if (showCurrSymbol) {
      // 'i' vs. 'n' ('USD' vs. '$')
      symbol = conversion === 'i' ? monetary.int_curr_symbol : monetary.currency_symbol
    }
    const signPosn = neg ? monetary.n_sign_posn : monetary.p_sign_posn

    // 0: no space between curr. symbol and value
    // 1: space sep. them unless symb. and sign are adjacent then space sep. them from value
    // 2: space sep. sign and value unless symb. and sign are adjacent then space separates
    const sepBySpace = neg ? monetary.n_sep_by_space : monetary.p_sep_by_space

    // p_cs_precedes, n_cs_precedes
    // positive currency symbol follows value = 0; precedes value = 1
    const csPrecedes = neg ? monetary.n_cs_precedes : monetary.p_cs_precedes

    // Assemble symbol/value/sign and possible space as appropriate
    if (flags.indexOf('(') !== -1) {
      // flag: parenth. for negative
      // @todo: unclear on whether and how sepBySpace, signPosn, or csPrecedes have
      // an impact here (as they do below), but assuming for now behaves as signPosn 0 as
      // far as localized sepBySpace and signPosn behavior
      repl = (csPrecedes ? symbol + (sepBySpace === 1 ? ' ' : '') : '') + value + (!csPrecedes
        ? (sepBySpace === 1 ? ' ' : '') + symbol
        : '')
      if (neg) {
        repl = '(' + repl + ')'
      } else {
        repl = ' ' + repl + ' '
      }
    } else {
      // '+' is default
      // ''
      const posSign = monetary.positive_sign
      // '-'
      const negSign = monetary.negative_sign
      const sign = neg ? (negSign) : (posSign)
      const otherSign = neg ? (posSign) : (negSign)
      let signPadding = ''
      if (signPosn) {
        // has a sign
        signPadding = new Array(otherSign.length - sign.length + 1).join(' ')
      }

      let valueAndCS = ''
      switch (signPosn) {
        // 0: parentheses surround value and curr. symbol;
        // 1: sign precedes them;
        // 2: sign follows them;
        // 3: sign immed. precedes curr. symbol; (but may be space between)
        // 4: sign immed. succeeds curr. symbol; (but may be space between)
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

    let padding = width - repl.length
    if (padding > 0) {
      padding = new Array(padding + 1).join(' ')
      // @todo: How does p_sep_by_space affect the count if there is a space?
      // Included in count presumably?
      if (flags.indexOf('-') !== -1) {
        // left-justified (pad to right)
        repl += padding
      } else {
        // right-justified (pad to left)
        repl = padding + repl
      }
    }
    return repl
  }

  return format.replace(regex, doReplace)
}
