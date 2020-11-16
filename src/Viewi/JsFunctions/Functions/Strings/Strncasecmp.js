function strncasecmp (argStr1, argStr2, len) {
  var diff
  var i = 0
  var str1 = (argStr1 + '').toLowerCase().substr(0, len)
  var str2 = (argStr2 + '').toLowerCase().substr(0, len)
  if (str1.length !== str2.length) {
    if (str1.length < str2.length) {
      len = str1.length
      if (str2.substr(0, str1.length) === str1) {
        return str1.length - str2.length
      }
    } else {
      len = str2.length
      if (str1.substr(0, str2.length) === str2) {
        return str1.length - str2.length
      }
    }
  } else {
    len = str1.length
  }
  for (diff = 0, i = 0; i < len; i++) {
    diff = str1.charCodeAt(i) - str2.charCodeAt(i)
    if (diff !== 0) {
      return diff
    }
  }
  return 0
}
