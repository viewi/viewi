function strstr (haystack, needle, bool) {
  var pos = 0
  haystack += ''
  pos = haystack.indexOf(needle)
  if (pos === -1) {
    return false
  } else {
    if (bool) {
      return haystack.substr(0, pos)
    } else {
      return haystack.slice(pos)
    }
  }
}
