function xdiff_string_patch (originalStr, patch, flags, errorObj) { 
  var _getNativeFlags = function (regex) {
    return [
      (regex.global ? 'g' : ''),
      (regex.ignoreCase ? 'i' : ''),
      (regex.multiline ? 'm' : ''),
      (regex.extended ? 'x' : ''),
      (regex.sticky ? 'y' : '')
    ].join('')
  }
  var _cbSplit = function (string, sep) {
    if (!(sep instanceof RegExp)) {
      return String.prototype.split.apply(string, arguments)
    }
    var str = String(string)
    var output = []
    var lastLastIndex = 0
    var match
    var lastLength
    var limit = Infinity
    var x = sep._xregexp
    var s = new RegExp(sep.source, _getNativeFlags(sep) + 'g')
    if (x) {
      s._xregexp = {
        source: x.source,
        captureNames: x.captureNames ? x.captureNames.slice(0) : null
      }
    }
    while ((match = s.exec(str))) {
      if (s.lastIndex > lastLastIndex) {
        output.push(str.slice(lastLastIndex, match.index))
        if (match.length > 1 && match.index < str.length) {
          Array.prototype.push.apply(output, match.slice(1))
        }
        lastLength = match[0].length
        lastLastIndex = s.lastIndex
        if (output.length >= limit) {
          break
        }
      }
      if (s.lastIndex === match.index) {
        s.lastIndex++
      }
    }
    if (lastLastIndex === str.length) {
      if (!s.test('') || lastLength) {
        output.push('')
      }
    } else {
      output.push(str.slice(lastLastIndex))
    }
    return output.length > limit ? output.slice(0, limit) : output
  }
  var i = 0
  var ll = 0
  var ranges = []
  var lastLinePos = 0
  var firstChar = ''
  var rangeExp = /^@@\s+-(\d+),(\d+)\s+\+(\d+),(\d+)\s+@@$/
  var lineBreaks = /\r?\n/
  var lines = _cbSplit(patch.replace(/(\r?\n)+$/, ''), lineBreaks)
  var origLines = _cbSplit(originalStr, lineBreaks)
  var newStrArr = []
  var linePos = 0
  var errors = ''
  var optTemp = 0 
  var OPTS = {
    'XDIFF_PATCH_NORMAL': 1,
    'XDIFF_PATCH_REVERSE': 2,
    'XDIFF_PATCH_IGNORESPACE': 4
  }
  if (typeof originalStr !== 'string' || !patch) {
    return false
  }
  if (!flags) {
    flags = 'XDIFF_PATCH_NORMAL'
  }
  if (typeof flags !== 'number') {
    flags = [].concat(flags)
    for (i = 0; i < flags.length; i++) {
      if (OPTS[flags[i]]) {
        optTemp = optTemp | OPTS[flags[i]]
      }
    }
    flags = optTemp
  }
  if (flags & OPTS.XDIFF_PATCH_NORMAL) {
    for (i = 0, ll = lines.length; i < ll; i++) {
      ranges = lines[i].match(rangeExp)
      if (ranges) {
        lastLinePos = linePos
        linePos = ranges[1] - 1
        while (lastLinePos < linePos) {
          newStrArr[newStrArr.length] = origLines[lastLinePos++]
        }
        while (lines[++i] && (rangeExp.exec(lines[i])) === null) {
          firstChar = lines[i].charAt(0)
          switch (firstChar) {
            case '-':
              ++linePos
              break
            case '+':
              newStrArr[newStrArr.length] = lines[i].slice(1)
              break
            case ' ':
              newStrArr[newStrArr.length] = origLines[linePos++]
              break
            default:
              throw new Error('Unrecognized initial character in unidiff line')
          }
        }
        if (lines[i]) {
          i--
        }
      }
    }
    while (linePos > 0 && linePos < origLines.length) {
      newStrArr[newStrArr.length] = origLines[linePos++]
    }
  } else if (flags & OPTS.XDIFF_PATCH_REVERSE) {
    for (i = 0, ll = lines.length; i < ll; i++) {
      ranges = lines[i].match(rangeExp)
      if (ranges) {
        lastLinePos = linePos
        linePos = ranges[3] - 1
        while (lastLinePos < linePos) {
          newStrArr[newStrArr.length] = origLines[lastLinePos++]
        }
        while (lines[++i] && (rangeExp.exec(lines[i])) === null) {
          firstChar = lines[i].charAt(0)
          switch (firstChar) {
            case '-':
              newStrArr[newStrArr.length] = lines[i].slice(1)
              break
            case '+':
              ++linePos
              break
            case ' ':
              newStrArr[newStrArr.length] = origLines[linePos++]
              break
            default:
              throw new Error('Unrecognized initial character in unidiff line')
          }
        }
        if (lines[i]) {
          i--
        }
      }
    }
    while (linePos > 0 && linePos < origLines.length) {
      newStrArr[newStrArr.length] = origLines[linePos++]
    }
  }
  if (errorObj) {
    errorObj.value = errors
  }
  return newStrArr.join('\n')
}
