function preg_quote(str, delimiter) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.preg-quote.php
  // Escapes . \ + * ? [ ^ ] $ ( ) { } = ! < > | : - # / and the delimiter; \0 becomes \000.
  str = _phpCastString(str)
  const special = '.\\+*?[^]$(){}=!<>|:-#/'
  let out = ''
  for (const ch of str) {
    if (ch === '\0') {
      out += '\\000'
    } else if (special.indexOf(ch) !== -1 || (delimiter && ch === delimiter[0])) {
      out += '\\' + ch
    } else {
      out += ch
    }
  }
  return out
}
