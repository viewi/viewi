function strtr(str, from, to) {
  //  discuss at: https://www.php.net/manual/en/function.strtr.php
  // strtr($s, $from, $to): character by character, over the shorter of the two lists.
  // strtr($s, $pairs): longest key first, and replaced text is never replaced again
  // (strtr('aaa', ['a' => 'b', 'aa' => 'c']) is 'cb'). An empty key is ignored.
  str = _phpCastString(str)
  if (to !== undefined) {
    from = _phpCastString(from)
    to = _phpCastString(to)
    const n = Math.min(from.length, to.length)
    let out = ''
    for (const ch of str) {
      const i = from.slice(0, n).lastIndexOf(ch)
      out += i === -1 ? ch : to[i]
    }
    return out
  }
  const pairs = _php_array_entries(from)
    .map(function (entry) {
      return [_phpCastString(entry[0]), _phpCastString(entry[1])]
    })
    .filter(function (pair) {
      return pair[0] !== ''
    })
    .sort(function (a, b) {
      return b[0].length - a[0].length
    })
  let out = ''
  let i = 0
  while (i < str.length) {
    let matched = false
    for (const pair of pairs) {
      if (str.startsWith(pair[0], i)) {
        out += pair[1]
        i += pair[0].length
        matched = true
        break
      }
    }
    if (!matched) {
      out += str[i++]
    }
  }
  return out
}
