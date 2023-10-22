function strrchr (haystack, needle) {
  //  discuss at: https://locutus.io/php/strrchr/
  // original by: Brett Zamir (https://brett-zamir.me)
  //    input by: Jason Wong (https://carrot.org/)
  // bugfixed by: Brett Zamir (https://brett-zamir.me)
  //   example 1: strrchr("Line 1\nLine 2\nLine 3", 10).substr(1)
  //   returns 1: 'Line 3'

  let pos = 0

  if (typeof needle !== 'string') {
    needle = String.fromCharCode(parseInt(needle, 10))
  }
  needle = needle.charAt(0)
  pos = haystack.lastIndexOf(needle)
  if (pos === -1) {
    return false
  }

  return haystack.substr(pos)
}
