function substr_compare (mainStr, str, offset, length, caseInsensitivity) { 
  if (!offset && offset !== 0) {
    throw new Error('Missing offset for substr_compare()')
  }
  if (offset < 0) {
    offset = mainStr.length + offset
  }
  if (length && length > (mainStr.length - offset)) {
    return false
  }
  length = length || mainStr.length - offset
  mainStr = mainStr.substr(offset, length)
  str = str.substr(0, length)
  if (caseInsensitivity) {
    mainStr = (mainStr + '').toLowerCase()
    str = (str + '').toLowerCase()
    if (mainStr === str) {
      return 0
    }
    return (mainStr > str) ? 1 : -1
  }
  return ((mainStr === str) ? 0 : ((mainStr > str) ? 1 : -1))
}
