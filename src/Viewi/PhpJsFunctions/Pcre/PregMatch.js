function preg_match(regex, str, matches) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.preg-match.php
  // Returns 1 or 0, as PHP does. If matches is an array it is filled in place: [whole match,
  // group 1, …], unmatched groups as '' and trailing unmatched ones dropped, as PHP does.
  // Modifiers i, m, s, u carry over; PHP-only ones (x, U, D, A) are ignored. Named groups are not
  // added as keys.
  const opening = regex[0]
  const closing = { '(': ')', '{': '}', '[': ']', '<': '>' }[opening] || opening
  const end = regex.lastIndexOf(closing)
  const pattern = regex.slice(1, end)
  const flags = regex.slice(end + 1).replace(/[^imsu]/g, '')
  const found = new RegExp(pattern, flags).exec(_phpCastString(str))
  if (Array.isArray(matches)) {
    const groups = found ? Array.from(found) : []
    while (groups.length > 1 && groups[groups.length - 1] === undefined) {
      groups.pop()
    }
    _php_array_set(matches, groups.map(function (group) {
      return group === undefined ? '' : group
    }))
  }
  return found ? 1 : 0
}
