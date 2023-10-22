function strcasecmp (fString1, fString2) {
  //  discuss at: https://locutus.io/php/strcasecmp/
  // original by: Martijn Wieringa
  // bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
  //   example 1: strcasecmp('Hello', 'hello')
  //   returns 1: 0

  const string1 = (fString1 + '').toLowerCase()
  const string2 = (fString2 + '').toLowerCase()

  if (string1 > string2) {
    return 1
  } else if (string1 === string2) {
    return 0
  }

  return -1
}
