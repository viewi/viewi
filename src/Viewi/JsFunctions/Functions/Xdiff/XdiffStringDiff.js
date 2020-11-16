function xdiff_string_diff (oldData, newData, contextLines, minimal) { 
  var i = 0
  var j = 0
  var k = 0
  var oriHunkStart
  var newHunkStart
  var oriHunkEnd
  var newHunkEnd
  var oriHunkLineNo
  var newHunkLineNo
  var oriHunkSize
  var newHunkSize
  var MAX_CONTEXT_LINES = Number.POSITIVE_INFINITY 
  var MIN_CONTEXT_LINES = 0
  var DEFAULT_CONTEXT_LINES = 3
  var HEADER_PREFIX = '@@ ' 
  var HEADER_SUFFIX = ' @@'
  var ORIGINAL_INDICATOR = '-'
  var NEW_INDICATOR = '+'
  var RANGE_SEPARATOR = ','
  var CONTEXT_INDICATOR = ' '
  var DELETION_INDICATOR = '-'
  var ADDITION_INDICATOR = '+'
  var oriLines
  var newLines
  var NEW_LINE = '\n'
  var _trim = function (text) {
    if (typeof text !== 'string') {
      throw new Error('String parameter required')
    }
    return text.replace(/(^\s*)|(\s*$)/g, '')
  }
  var _verifyType = function (type) {
    var args = arguments
    var argsLen = arguments.length
    var basicTypes = ['number', 'boolean', 'string', 'function', 'object', 'undefined']
    var basicType
    var i
    var j
    var typeOfType = typeof type
    if (typeOfType !== 'string' && typeOfType !== 'function') {
      throw new Error('Bad type parameter')
    }
    if (argsLen < 2) {
      throw new Error('Too few arguments')
    }
    if (typeOfType === 'string') {
      type = _trim(type)
      if (type === '') {
        throw new Error('Bad type parameter')
      }
      for (j = 0; j < basicTypes.length; j++) {
        basicType = basicTypes[j]
        if (basicType === type) {
          for (i = 1; i < argsLen; i++) {
            if (typeof args[i] !== type) {
              throw new Error('Bad type')
            }
          }
          return
        }
      }
      throw new Error('Bad type parameter')
    }
    for (i = 1; i < argsLen; i++) {
      if (!(args[i] instanceof type)) {
        throw new Error('Bad type')
      }
    }
  }
  var _hasValue = function (array, value) {
    var i
    _verifyType(Array, array)
    for (i = 0; i < array.length; i++) {
      if (array[i] === value) {
        return true
      }
    }
    return false
  }
  var _areTypeOf = function (type) {
    var args = arguments
    var argsLen = arguments.length
    var basicTypes = ['number', 'boolean', 'string', 'function', 'object', 'undefined']
    var basicType
    var i
    var j
    var typeOfType = typeof type
    if (typeOfType !== 'string' && typeOfType !== 'function') {
      throw new Error('Bad type parameter')
    }
    if (argsLen < 2) {
      throw new Error('Too few arguments')
    }
    if (typeOfType === 'string') {
      type = _trim(type)
      if (type === '') {
        return false
      }
      for (j = 0; j < basicTypes.length; j++) {
        basicType = basicTypes[j]
        if (basicType === type) {
          for (i = 1; i < argsLen; i++) {
            if (typeof args[i] !== type) {
              return false
            }
          }
          return true
        }
      }
      throw new Error('Bad type parameter')
    }
    for (i = 1; i < argsLen; i++) {
      if (!(args[i] instanceof type)) {
        return false
      }
    }
    return true
  }
  var _getInitializedArray = function (arraySize, initValue) {
    var array = []
    var i
    _verifyType('number', arraySize)
    for (i = 0; i < arraySize; i++) {
      array.push(initValue)
    }
    return array
  }
  var _splitIntoLines = function (text) {
    _verifyType('string', text)
    if (text === '') {
      return []
    }
    return text.split('\n')
  }
  var _isEmptyArray = function (obj) {
    return _areTypeOf(Array, obj) && obj.length === 0
  }
  var _findLongestCommonSequence = function (seq1, seq2, seq1IsInLcs, seq2IsInLcs) {
    if (!_areTypeOf(Array, seq1, seq2)) {
      throw new Error('Array parameters are required')
    }
    if (_isEmptyArray(seq1) || _isEmptyArray(seq2)) {
      return []
    }
    var lcsLens = function (xs, ys) {
      var i
      var j
      var prev
      var curr = _getInitializedArray(ys.length + 1, 0)
      for (i = 0; i < xs.length; i++) {
        prev = curr.slice(0)
        for (j = 0; j < ys.length; j++) {
          if (xs[i] === ys[j]) {
            curr[j + 1] = prev[j] + 1
          } else {
            curr[j + 1] = Math.max(curr[j], prev[j + 1])
          }
        }
      }
      return curr
    }
    var _findLcs = function (xs, xidx, xIsIn, ys) {
      var i
      var xb
      var xe
      var llB
      var llE
      var pivot
      var max
      var yb
      var ye
      var nx = xs.length
      var ny = ys.length
      if (nx === 0) {
        return []
      }
      if (nx === 1) {
        if (_hasValue(ys, xs[0])) {
          xIsIn[xidx] = true
          return [xs[0]]
        }
        return []
      }
      i = Math.floor(nx / 2)
      xb = xs.slice(0, i)
      xe = xs.slice(i)
      llB = lcsLens(xb, ys)
      llE = lcsLens(xe.slice(0)
        .reverse(), ys.slice(0)
        .reverse())
      pivot = 0
      max = 0
      for (j = 0; j <= ny; j++) {
        if (llB[j] + llE[ny - j] > max) {
          pivot = j
          max = llB[j] + llE[ny - j]
        }
      }
      yb = ys.slice(0, pivot)
      ye = ys.slice(pivot)
      return _findLcs(xb, xidx, xIsIn, yb).concat(_findLcs(xe, xidx + i, xIsIn, ye))
    }
    _findLcs(seq1, 0, seq1IsInLcs, seq2)
    return _findLcs(seq2, 0, seq2IsInLcs, seq1)
  }
  if (_areTypeOf('string', oldData, newData) === false) {
    return false
  }
  if (oldData === newData) {
    return ''
  }
  if (typeof contextLines !== 'number' ||
    contextLines > MAX_CONTEXT_LINES ||
    contextLines < MIN_CONTEXT_LINES) {
    contextLines = DEFAULT_CONTEXT_LINES
  }
  oriLines = _splitIntoLines(oldData)
  newLines = _splitIntoLines(newData)
  var oriLen = oriLines.length
  var newLen = newLines.length
  var oriIsInLcs = _getInitializedArray(oriLen, false)
  var newIsInLcs = _getInitializedArray(newLen, false)
  var lcsLen = _findLongestCommonSequence(oriLines, newLines, oriIsInLcs, newIsInLcs).length
  var unidiff = ''
  if (lcsLen === 0) {
    unidiff = [
      HEADER_PREFIX,
      ORIGINAL_INDICATOR,
      (oriLen > 0 ? '1' : '0'),
      RANGE_SEPARATOR,
      oriLen,
      ' ',
      NEW_INDICATOR,
      (newLen > 0 ? '1' : '0'),
      RANGE_SEPARATOR,
      newLen,
      HEADER_SUFFIX
    ].join('')
    for (i = 0; i < oriLen; i++) {
      unidiff += NEW_LINE + DELETION_INDICATOR + oriLines[i]
    }
    for (j = 0; j < newLen; j++) {
      unidiff += NEW_LINE + ADDITION_INDICATOR + newLines[j]
    }
    return unidiff
  }
  var leadingContext = []
  var trailingContext = []
  var actualLeadingContext = []
  var actualTrailingContext = []
  var regularizeLeadingContext = function (context) {
    if (context.length === 0 || contextLines === 0) {
      return []
    }
    var contextStartPos = Math.max(context.length - contextLines, 0)
    return context.slice(contextStartPos)
  }
  var regularizeTrailingContext = function (context) {
    if (context.length === 0 || contextLines === 0) {
      return []
    }
    return context.slice(0, Math.min(contextLines, context.length))
  }
  while (i < oriLen && oriIsInLcs[i] === true && newIsInLcs[i] === true) {
    leadingContext.push(oriLines[i])
    i++
  }
  j = i
  k = i
  oriHunkStart = i
  newHunkStart = j
  oriHunkEnd = i
  newHunkEnd = j
  while (i < oriLen || j < newLen) {
    while (i < oriLen && oriIsInLcs[i] === false) {
      i++
    }
    oriHunkEnd = i
    while (j < newLen && newIsInLcs[j] === false) {
      j++
    }
    newHunkEnd = j
    trailingContext = []
    while (i < oriLen && oriIsInLcs[i] === true && j < newLen && newIsInLcs[j] === true) {
      trailingContext.push(oriLines[i])
      k++
      i++
      j++
    }
    if (k >= lcsLen || 
      trailingContext.length >= 2 * contextLines) {
      if (trailingContext.length < 2 * contextLines) {
        trailingContext = []
        i = oriLen
        j = newLen
        oriHunkEnd = oriLen
        newHunkEnd = newLen
      }
      actualLeadingContext = regularizeLeadingContext(leadingContext)
      actualTrailingContext = regularizeTrailingContext(trailingContext)
      oriHunkStart -= actualLeadingContext.length
      newHunkStart -= actualLeadingContext.length
      oriHunkEnd += actualTrailingContext.length
      newHunkEnd += actualTrailingContext.length
      oriHunkLineNo = oriHunkStart + 1
      newHunkLineNo = newHunkStart + 1
      oriHunkSize = oriHunkEnd - oriHunkStart
      newHunkSize = newHunkEnd - newHunkStart
      unidiff += [
        HEADER_PREFIX,
        ORIGINAL_INDICATOR,
        oriHunkLineNo,
        RANGE_SEPARATOR,
        oriHunkSize,
        ' ',
        NEW_INDICATOR,
        newHunkLineNo,
        RANGE_SEPARATOR,
        newHunkSize,
        HEADER_SUFFIX,
        NEW_LINE
      ].join('')
      while (oriHunkStart < oriHunkEnd || newHunkStart < newHunkEnd) {
        if (oriHunkStart < oriHunkEnd &&
          oriIsInLcs[oriHunkStart] === true &&
          newIsInLcs[newHunkStart] === true) {
          unidiff += CONTEXT_INDICATOR + oriLines[oriHunkStart] + NEW_LINE
          oriHunkStart++
          newHunkStart++
        } else if (oriHunkStart < oriHunkEnd && oriIsInLcs[oriHunkStart] === false) {
          unidiff += DELETION_INDICATOR + oriLines[oriHunkStart] + NEW_LINE
          oriHunkStart++
        } else if (newHunkStart < newHunkEnd && newIsInLcs[newHunkStart] === false) {
          unidiff += ADDITION_INDICATOR + newLines[newHunkStart] + NEW_LINE
          newHunkStart++
        }
      }
      oriHunkStart = i
      newHunkStart = j
      leadingContext = trailingContext
    }
  }
  if (unidiff.length > 0 && unidiff.charAt(unidiff.length) === NEW_LINE) {
    unidiff = unidiff.slice(0, -1)
  }
  return unidiff
}
