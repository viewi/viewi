function substr_replace (str, replace, start, length) { 
  if (start < 0) {
    start = start + str.length
  }
  length = length !== undefined ? length : str.length
  if (length < 0) {
    length = length + str.length - start
  }
  return [
    str.slice(0, start),
    replace.substr(0, length),
    replace.slice(length),
    str.slice(start + length)
  ].join('')
}
