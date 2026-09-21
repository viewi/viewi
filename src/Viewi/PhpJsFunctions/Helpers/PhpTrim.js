/**
 * trim/ltrim/rtrim with PHP's rules: the default set is exactly " \t\n\r\0\x0B" and a charlist
 * may hold ranges ("a..z"). sides: 1 = left, 2 = right, 3 = both.
 */
function _php_trim(str, charlist, sides) {
  str = _phpCastString(str)
  let mask = ' \t\n\r\0\x0B'
  if (charlist !== undefined && charlist !== null) {
    const list = _phpCastString(charlist)
    mask = ''
    for (let i = 0; i < list.length; i++) {
      if (list.substr(i + 1, 2) === '..' && i + 3 < list.length && list.charCodeAt(i + 3) >= list.charCodeAt(i)) {
        for (let c = list.charCodeAt(i); c <= list.charCodeAt(i + 3); c++) {
          mask += String.fromCharCode(c)
        }
        i += 3
      } else {
        mask += list[i]
      }
    }
  }
  let start = 0
  let end = str.length
  if (sides & 1) {
    while (start < end && mask.indexOf(str[start]) !== -1) {
      start++
    }
  }
  if (sides & 2) {
    while (end > start && mask.indexOf(str[end - 1]) !== -1) {
      end--
    }
  }
  return str.slice(start, end)
}
