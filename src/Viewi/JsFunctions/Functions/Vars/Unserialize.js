function initCache () {
  const store = []
  const cache = function cache (value) {
    store.push(value[0])
    return value
  }
  cache.get = (index) => {
    if (index >= store.length) {
      throw RangeError(`Can't resolve reference ${index + 1}`)
    }
    return store[index]
  }
  return cache
}
function expectType (str, cache) {
  const types = /^(?:N(?=;)|[bidsSaOCrR](?=:)|[^:]+(?=:))/g
  const type = (types.exec(str) || [])[0]
  if (!type) {
    throw SyntaxError('Invalid input: ' + str)
  }
  switch (type) {
    case 'N':
      return cache([ null, 2 ])
    case 'b':
      return cache(expectBool(str))
    case 'i':
      return cache(expectInt(str))
    case 'd':
      return cache(expectFloat(str))
    case 's':
      return cache(expectString(str))
    case 'S':
      return cache(expectEscapedString(str))
    case 'a':
      return expectArray(str, cache)
    case 'O':
      return expectObject(str, cache)
    case 'C':
      return expectClass(str, cache)
    case 'r':
    case 'R':
      return expectReference(str, cache)
    default:
      throw SyntaxError(`Invalid or unsupported data type: ${type}`)
  }
}
function expectBool (str) {
  const reBool = /^b:([01]);/
  const [ match, boolMatch ] = reBool.exec(str) || []
  if (!boolMatch) {
    throw SyntaxError('Invalid bool value, expected 0 or 1')
  }
  return [ boolMatch === '1', match.length ]
}
function expectInt (str) {
  const reInt = /^i:([+-]?\d+);/
  const [ match, intMatch ] = reInt.exec(str) || []
  if (!intMatch) {
    throw SyntaxError('Expected an integer value')
  }
  return [ parseInt(intMatch, 10), match.length ]
}
function expectFloat (str) {
  const reFloat = /^d:(NAN|-?INF|(?:\d+\.\d*|\d*\.\d+|\d+)(?:[eE][+-]\d+)?);/
  const [ match, floatMatch ] = reFloat.exec(str) || []
  if (!floatMatch) {
    throw SyntaxError('Expected a float value')
  }
  let floatValue
  switch (floatMatch) {
    case 'NAN':
      floatValue = Number.NaN
      break
    case '-INF':
      floatValue = Number.NEGATIVE_INFINITY
      break
    case 'INF':
      floatValue = Number.POSITIVE_INFINITY
      break
    default:
      floatValue = parseFloat(floatMatch)
      break
  }
  return [ floatValue, match.length ]
}
function readBytes (str, len, escapedString = false) {
  let bytes = 0
  let out = ''
  let c = 0
  const strLen = str.length
  let wasHighSurrogate = false
  let escapedChars = 0
  while (bytes < len && c < strLen) {
    let chr = str.charAt(c)
    const code = chr.charCodeAt(0)
    const isHighSurrogate = code >= 0xd800 && code <= 0xdbff
    const isLowSurrogate = code >= 0xdc00 && code <= 0xdfff
    if (escapedString && chr === '\\') {
      chr = String.fromCharCode(parseInt(str.substr(c + 1, 2), 16))
      escapedChars++
      c += 2
    }
    c++
    bytes += isHighSurrogate || (isLowSurrogate && wasHighSurrogate)
      ? 2
      : code > 0x7ff
        ? 3
        : code > 0x7f
          ? 2
          : 1
    bytes += wasHighSurrogate && !isLowSurrogate ? 1 : 0
    out += chr
    wasHighSurrogate = isHighSurrogate
  }
  return [ out, bytes, escapedChars ]
}
function expectString (str) {
  const reStrLength = /^s:(\d+):"/g 
  const [ match, byteLenMatch ] = reStrLength.exec(str) || []
  if (!match) {
    throw SyntaxError('Expected a string value')
  }
  const len = parseInt(byteLenMatch, 10)
  str = str.substr(match.length)
  let [ strMatch, bytes ] = readBytes(str, len)
  if (bytes !== len) {
    throw SyntaxError(`Expected string of ${len} bytes, but got ${bytes}`)
  }
  str = str.substr(strMatch.length)
  if (!str.startsWith('";')) {
    throw SyntaxError('Expected ";')
  }
  return [ strMatch, match.length + strMatch.length + 2 ] 
}
function expectEscapedString (str) {
  const reStrLength = /^S:(\d+):"/g 
  const [ match, strLenMatch ] = reStrLength.exec(str) || []
  if (!match) {
    throw SyntaxError('Expected an escaped string value')
  }
  const len = parseInt(strLenMatch, 10)
  str = str.substr(match.length)
  let [ strMatch, bytes, escapedChars ] = readBytes(str, len, true)
  if (bytes !== len) {
    throw SyntaxError(`Expected escaped string of ${len} bytes, but got ${bytes}`)
  }
  str = str.substr(strMatch.length + escapedChars * 2)
  if (!str.startsWith('";')) {
    throw SyntaxError('Expected ";')
  }
  return [ strMatch, match.length + strMatch.length + 2 ] 
}
function expectKeyOrIndex (str) {
  try {
    return expectString(str)
  } catch (err) {}
  try {
    return expectEscapedString(str)
  } catch (err) {}
  try {
    return expectInt(str)
  } catch (err) {
    throw SyntaxError('Expected key or index')
  }
}
function expectObject (str, cache) {
  const reObjectLiteral = /^O:(\d+):"([^"]+)":(\d+):\{/
  const [ objectLiteralBeginMatch, , className, propCountMatch ] = reObjectLiteral.exec(str) || []
  if (!objectLiteralBeginMatch) {
    throw SyntaxError('Invalid input')
  }
  if (className !== 'stdClass') {
    throw SyntaxError(`Unsupported object type: ${className}`)
  }
  let totalOffset = objectLiteralBeginMatch.length
  const propCount = parseInt(propCountMatch, 10)
  const obj = {}
  cache([obj])
  str = str.substr(totalOffset)
  for (let i = 0; i < propCount; i++) {
    const prop = expectKeyOrIndex(str)
    str = str.substr(prop[1])
    totalOffset += prop[1]
    const value = expectType(str, cache)
    str = str.substr(value[1])
    totalOffset += value[1]
    obj[prop[0]] = value[0]
  }
  if (str.charAt(0) !== '}') {
    throw SyntaxError('Expected }')
  }
  return [ obj, totalOffset + 1 ] 
}
function expectClass (str, cache) {
  throw Error('Not yet implemented')
}
function expectReference (str, cache) {
  const reRef = /^[rR]:([1-9]\d*);/
  const [ match, refIndex ] = reRef.exec(str) || []
  if (!match) {
    throw SyntaxError('Expected reference value')
  }
  return [ cache.get(parseInt(refIndex, 10) - 1), match.length ]
}
function expectArray (str, cache) {
  const reArrayLength = /^a:(\d+):{/
  const [ arrayLiteralBeginMatch, arrayLengthMatch ] = reArrayLength.exec(str) || []
  if (!arrayLengthMatch) {
    throw SyntaxError('Expected array length annotation')
  }
  str = str.substr(arrayLiteralBeginMatch.length)
  const array = expectArrayItems(str, parseInt(arrayLengthMatch, 10), cache)
  if (str.charAt(array[1]) !== '}') {
    throw SyntaxError('Expected }')
  }
  return [ array[0], arrayLiteralBeginMatch.length + array[1] + 1 ] 
}
function expectArrayItems (str, expectedItems = 0, cache) {
  let key
  let hasStringKeys = false
  let item
  let totalOffset = 0
  let items = []
  cache([items])
  for (let i = 0; i < expectedItems; i++) {
    key = expectKeyOrIndex(str)
    if (!hasStringKeys) {
      hasStringKeys = (typeof key[0] === 'string')
    }
    str = str.substr(key[1])
    totalOffset += key[1]
    item = expectType(str, cache)
    str = str.substr(item[1])
    totalOffset += item[1]
    items[key[0]] = item[0]
  }
  if (hasStringKeys) {
    items = Object.assign({}, items)
  }
  return [ items, totalOffset ]
}
function unserialize (str) {
  try {
    if (typeof str !== 'string') {
      return false
    }
    return expectType(str, initCache())[0]
  } catch (err) {
    console.error(err)
    return false
  }
}
