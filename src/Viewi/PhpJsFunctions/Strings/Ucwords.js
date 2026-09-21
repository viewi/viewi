function ucwords(str, delimiters) {
  //  discuss at: https://www.php.net/manual/en/function.ucwords.php
  // Upper-cases the first character of the string and of every character after a delimiter
  // (default: space, \t, \r, \n, \f, \v). ASCII only, as since PHP 8.2.
  str = _phpCastString(str)
  delimiters = delimiters === undefined ? ' \t\r\n\f\v' : _phpCastString(delimiters)
  let out = ''
  for (let i = 0; i < str.length; i++) {
    const ch = str[i]
    const start = i === 0 || delimiters.indexOf(str[i - 1]) !== -1
    out += start && ch >= 'a' && ch <= 'z' ? String.fromCharCode(ch.charCodeAt(0) - 32) : ch
  }
  return out
}
