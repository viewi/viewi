function addcslashes (str, charlist) {
  var target = ''
  var chrs = []
  var i = 0
  var j = 0
  var c = ''
  var next = ''
  var rangeBegin = ''
  var rangeEnd = ''
  var chr = ''
  var begin = 0
  var end = 0
  var octalLength = 0
  var postOctalPos = 0
  var cca = 0
  var escHexGrp = []
  var encoded = ''
  var percentHex = /%([\dA-Fa-f]+)/g
  var _pad = function (n, c) {
    if ((n = n + '').length < c) {
      return new Array(++c - n.length).join('0') + n
    }
    return n
  }
  for (i = 0; i < charlist.length; i++) {
    c = charlist.charAt(i)
    next = charlist.charAt(i + 1)
    if (c === '\\' && next && (/\d/).test(next)) {
      rangeBegin = charlist.slice(i + 1).match(/^\d+/)[0]
      octalLength = rangeBegin.length
      postOctalPos = i + octalLength + 1
      if (charlist.charAt(postOctalPos) + charlist.charAt(postOctalPos + 1) === '..') {
        begin = rangeBegin.charCodeAt(0)
        if ((/\\\d/).test(charlist.charAt(postOctalPos + 2) + charlist.charAt(postOctalPos + 3))) {
          rangeEnd = charlist.slice(postOctalPos + 3).match(/^\d+/)[0]
          i += 1
        } else if (charlist.charAt(postOctalPos + 2)) {
          rangeEnd = charlist.charAt(postOctalPos + 2)
        } else {
          throw new Error('Range with no end point')
        }
        end = rangeEnd.charCodeAt(0)
        if (end > begin) {
          for (j = begin; j <= end; j++) {
            chrs.push(String.fromCharCode(j))
          }
        } else {
          chrs.push('.', rangeBegin, rangeEnd)
        }
        i += rangeEnd.length + 2
      } else {
        chr = String.fromCharCode(parseInt(rangeBegin, 8))
        chrs.push(chr)
      }
      i += octalLength
    } else if (next + charlist.charAt(i + 2) === '..') {
      rangeBegin = c
      begin = rangeBegin.charCodeAt(0)
      if ((/\\\d/).test(charlist.charAt(i + 3) + charlist.charAt(i + 4))) {
        rangeEnd = charlist.slice(i + 4).match(/^\d+/)[0]
        i += 1
      } else if (charlist.charAt(i + 3)) {
        rangeEnd = charlist.charAt(i + 3)
      } else {
        throw new Error('Range with no end point')
      }
      end = rangeEnd.charCodeAt(0)
      if (end > begin) {
        for (j = begin; j <= end; j++) {
          chrs.push(String.fromCharCode(j))
        }
      } else {
        chrs.push('.', rangeBegin, rangeEnd)
      }
      i += rangeEnd.length + 2
    } else {
      chrs.push(c)
    }
  }
  for (i = 0; i < str.length; i++) {
    c = str.charAt(i)
    if (chrs.indexOf(c) !== -1) {
      target += '\\'
      cca = c.charCodeAt(0)
      if (cca < 32 || cca > 126) {
        switch (c) {
          case '\n':
            target += 'n'
            break
          case '\t':
            target += 't'
            break
          case '\u000D':
            target += 'r'
            break
          case '\u0007':
            target += 'a'
            break
          case '\v':
            target += 'v'
            break
          case '\b':
            target += 'b'
            break
          case '\f':
            target += 'f'
            break
          default:
            encoded = encodeURIComponent(c)
            if ((escHexGrp = percentHex.exec(encoded)) !== null) {
              target += _pad(parseInt(escHexGrp[1], 16).toString(8), 3)
            }
            while ((escHexGrp = percentHex.exec(encoded)) !== null) {
              target += '\\' + _pad(parseInt(escHexGrp[1], 16).toString(8), 3)
            }
            break
        }
      } else {
        target += c
      }
    } else {
      target += c
    }
  }
  return target
}
