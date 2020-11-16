function quoted_printable_decode (str) { 
  var RFC2045Decode1 = /=\r\n/gm
  var RFC2045Decode2IN = /=([0-9A-F]{2})/gim
  var RFC2045Decode2OUT = function (sMatch, sHex) {
    return String.fromCharCode(parseInt(sHex, 16))
  }
  return str.replace(RFC2045Decode1, '')
    .replace(RFC2045Decode2IN, RFC2045Decode2OUT)
}
