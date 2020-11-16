function strpbrk (haystack, charList) {
  for (var i = 0, len = haystack.length; i < len; ++i) {
    if (charList.indexOf(haystack.charAt(i)) >= 0) {
      return haystack.slice(i)
    }
  }
  return false
}
