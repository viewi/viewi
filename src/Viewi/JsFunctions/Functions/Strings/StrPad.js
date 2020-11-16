function str_pad (input, padLength, padString, padType) { 
  var half = ''
  var padToGo
  var _strPadRepeater = function (s, len) {
    var collect = ''
    while (collect.length < len) {
      collect += s
    }
    collect = collect.substr(0, len)
    return collect
  }
  input += ''
  padString = padString !== undefined ? padString : ' '
  if (padType !== 'STR_PAD_LEFT' && padType !== 'STR_PAD_RIGHT' && padType !== 'STR_PAD_BOTH') {
    padType = 'STR_PAD_RIGHT'
  }
  if ((padToGo = padLength - input.length) > 0) {
    if (padType === 'STR_PAD_LEFT') {
      input = _strPadRepeater(padString, padToGo) + input
    } else if (padType === 'STR_PAD_RIGHT') {
      input = input + _strPadRepeater(padString, padToGo)
    } else if (padType === 'STR_PAD_BOTH') {
      half = _strPadRepeater(padString, Math.ceil(padToGo / 2))
      input = half + input + half
      input = input.substr(0, padLength)
    }
  }
  return input
}
