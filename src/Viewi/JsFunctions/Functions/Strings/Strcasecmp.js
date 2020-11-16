function strcasecmp (fString1, fString2) {
  var string1 = (fString1 + '').toLowerCase()
  var string2 = (fString2 + '').toLowerCase()
  if (string1 > string2) {
    return 1
  } else if (string1 === string2) {
    return 0
  }
  return -1
}
