function quoted_printable_encode (str) { 
  var hexChars = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F']
  var RFC2045Encode1IN = / \r\n|\r\n|[^!-<>-~ ]/gm
  var RFC2045Encode1OUT = function (sMatch) {
    if (sMatch.length > 1) {
      return sMatch.replace(' ', '=20')
    }
    var chr = sMatch.charCodeAt(0)
    return '=' + hexChars[((chr >>> 4) & 15)] + hexChars[(chr & 15)]
  }
  var RFC2045Encode2IN = /.{1,72}(?!\r\n)[^=]{0,3}/g
  var RFC2045Encode2OUT = function (sMatch) {
    if (sMatch.substr(sMatch.length - 2) === '\r\n') {
      return sMatch
    }
    return sMatch + '=\r\n'
  }
  str = str
    .replace(RFC2045Encode1IN, RFC2045Encode1OUT)
    .replace(RFC2045Encode2IN, RFC2045Encode2OUT)
  return str.substr(0, str.length - 3)
}
