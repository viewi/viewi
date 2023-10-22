function stripos (fHaystack, fNeedle, fOffset) {
  //  discuss at: https://locutus.io/php/stripos/
  // original by: Martijn Wieringa
  //  revised by: Onno Marsman (https://twitter.com/onnomarsman)
  //   example 1: stripos('ABC', 'a')
  //   returns 1: 0

  const haystack = (fHaystack + '').toLowerCase()
  const needle = (fNeedle + '').toLowerCase()
  let index = 0

  if ((index = haystack.indexOf(needle, fOffset)) !== -1) {
    return index
  }

  return false
}
