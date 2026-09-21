function preg_replace(pattern, replacement, subject, limit) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.preg-replace.php
  // pattern/replacement/subject may be arrays (patterns applied in order; a missing replacement
  // is ''). The replacement uses PHP's references: $1, ${1}, \1 (not JS's $&). limit -1 = all.
  limit = limit === undefined || limit === null ? -1 : _php_cast_int(limit)
  const patterns = Array.isArray(pattern) ? pattern : [pattern]
  const replacements = Array.isArray(replacement) ? replacement : null
  const one = function (text) {
    text = _phpCastString(text)
    patterns.forEach(function (p, i) {
      const rep = _phpCastString(replacements ? (i < replacements.length ? replacements[i] : '') : replacement)
      const re = _php_regex(p, 'g')
      let count = 0
      text = text.replace(re, function () {
        const groups = Array.prototype.slice.call(arguments, 0, -2)
        if (typeof groups[groups.length - 1] === 'object') {
          groups.pop() // named groups object
        }
        if (limit >= 0 && count >= limit) {
          return groups[0]
        }
        count++
        return rep.replace(/\\\\|\$\{(\d{1,2})\}|\$(\d{1,2})|\\(\d{1,2})/g, function (m, a, b, c) {
          if (m === '\\\\') {
            return '\\'
          }
          const n = +(a || b || c)
          return groups[n] === undefined ? '' : groups[n]
        })
      })
    })
    return text
  }
  if (subject !== null && typeof subject === 'object') {
    return _php_array(_php_array_entries(subject).map(function (entry) {
      return [entry[0], one(entry[1])]
    }))
  }
  return one(subject)
}
