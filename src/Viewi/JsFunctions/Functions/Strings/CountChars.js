function count_chars (str, mode) { 
  var result = {}
  var resultArr = []
  var i
  str = ('' + str)
    .split('')
    .sort()
    .join('')
    .match(/(.)\1*/g)
  if ((mode & 1) === 0) {
    for (i = 0; i !== 256; i++) {
      result[i] = 0
    }
  }
  if (mode === 2 || mode === 4) {
    for (i = 0; i !== str.length; i += 1) {
      delete result[str[i].charCodeAt(0)]
    }
    for (i in result) {
      result[i] = (mode === 4) ? String.fromCharCode(i) : 0
    }
  } else if (mode === 3) {
    for (i = 0; i !== str.length; i += 1) {
      result[i] = str[i].slice(0, 1)
    }
  } else {
    for (i = 0; i !== str.length; i += 1) {
      result[str[i].charCodeAt(0)] = str[i].length
    }
  }
  if (mode < 3) {
    return result
  }
  for (i in result) {
    resultArr.push(result[i])
  }
  return resultArr.join('')
}
