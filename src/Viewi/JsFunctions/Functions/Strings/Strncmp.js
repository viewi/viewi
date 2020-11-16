function strncmp (str1, str2, lgth) {
  var s1 = (str1 + '')
    .substr(0, lgth)
  var s2 = (str2 + '')
    .substr(0, lgth)
  return ((s1 === s2) ? 0 : ((s1 > s2) ? 1 : -1))
}
