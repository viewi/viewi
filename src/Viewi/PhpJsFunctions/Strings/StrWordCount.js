function str_word_count(str, format, charlist) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.str-word-count.php
  // A word is ASCII letters plus ' and -, not starting with either (and not ending with -);
  // charlist adds characters. format 0: the count, 1: the words, 2: offset → word.
  str = _phpCastString(str)
  const extra = charlist === undefined || charlist === null ? '' : _phpCastString(charlist)
  const isWordChar = function (ch) {
    return (ch >= 'a' && ch <= 'z') || (ch >= 'A' && ch <= 'Z') || ch === "'" || ch === '-' ||
      extra.indexOf(ch) !== -1
  }
  const words = []
  let i = 0
  while (i < str.length) {
    // a word may not start with ' or - (unless the charlist allows it)
    while (i < str.length && (!isWordChar(str[i]) ||
      ((str[i] === "'" || str[i] === '-') && extra.indexOf(str[i]) === -1))) {
      i++
    }
    const start = i
    while (i < str.length && isWordChar(str[i])) {
      i++
    }
    let end = i
    if (end > start && str[end - 1] === '-' && extra.indexOf('-') === -1) {
      end-- // PHP drops a trailing hyphen
    }
    if (end > start) {
      words.push([start, str.slice(start, end)])
    }
  }
  if (format === 1) {
    return words.map(function (w) {
      return w[1]
    })
  }
  if (format === 2) {
    return _php_array(words)
  }
  return words.length
}
