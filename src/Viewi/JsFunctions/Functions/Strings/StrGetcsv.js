function str_getcsv (input, delimiter, enclosure, escape) { 
  var i
  var inpLen
  var output = []
  var _backwards = function (str) {
    return str.split('').reverse().join('')
  }
  var _pq = function (str) {
    return String(str).replace(/([\\.+*?[^\]$(){}=!<>|:])/g, '\\$1')
  }
  delimiter = delimiter || ','
  enclosure = enclosure || '"'
  escape = escape || '\\'
  var pqEnc = _pq(enclosure)
  var pqEsc = _pq(escape)
  input = input
    .replace(new RegExp('^\\s*' + pqEnc), '')
    .replace(new RegExp(pqEnc + '\\s*$'), '')
  input = _backwards(input)
    .split(new RegExp(pqEnc + '\\s*' + _pq(delimiter) + '\\s*' + pqEnc + '(?!' + pqEsc + ')', 'g'))
    .reverse()
  for (i = 0, inpLen = input.length; i < inpLen; i++) {
    output.push(_backwards(input[i])
      .replace(new RegExp(pqEsc + pqEnc, 'g'), enclosure))
  }
  return output
}
