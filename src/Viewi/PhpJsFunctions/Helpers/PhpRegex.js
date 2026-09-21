/**
 * A PHP regex ('/…/i', '#…#', '{…}') as a JS RegExp. Modifiers i, m, s, u carry over; PHP-only
 * ones (x, U, D, A) are dropped. extraFlags adds JS flags (e.g. 'g').
 */
function _php_regex(regex, extraFlags) {
  regex = _phpCastString(regex)
  const opening = regex[0]
  const closing = { '(': ')', '{': '}', '[': ']', '<': '>' }[opening] || opening
  const end = regex.lastIndexOf(closing)
  return new RegExp(regex.slice(1, end), regex.slice(end + 1).replace(/[^imsu]/g, '') + (extraFlags || ''))
}
