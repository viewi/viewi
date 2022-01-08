function print_r (array, returnVal) { 
  var echo = console.log
  var output = ''
  var padChar = ' '
  var padVal = 4
  var _repeatChar = function (len, padChar) {
    var str = ''
    for (var i = 0; i < len; i++) {
      str += padChar
    }
    return str
  }
  var _formatArray = function (obj, curDepth, padVal, padChar) {
    if (curDepth > 0) {
      curDepth++
    }
    var basePad = _repeatChar(padVal * curDepth, padChar)
    var thickPad = _repeatChar(padVal * (curDepth + 1), padChar)
    var str = ''
    if (typeof obj === 'object' &&
      obj !== null &&
      obj.constructor) {
      str += 'Array\n' + basePad + '(\n'
      for (var key in obj) {
        if (Object.prototype.toString.call(obj[key]) === '[object Array]') {
          str += thickPad
          str += '['
          str += key
          str += '] => '
          str += _formatArray(obj[key], curDepth + 1, padVal, padChar)
        } else {
          str += thickPad
          str += '['
          str += key
          str += '] => '
          str += obj[key]
          str += '\n'
        }
      }
      str += basePad + ')\n'
    } else if (obj === null || obj === undefined) {
      str = ''
    } else {
      str = obj.toString()
    }
    return str
  }
  output = _formatArray(array, 0, padVal, padChar)
  if (returnVal !== true) {
    echo(output)
    return true
  }
  return output
}
