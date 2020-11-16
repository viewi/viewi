function lcfirst (str) {
  str += ''
  var f = str.charAt(0)
    .toLowerCase()
  return f + str.substr(1)
}
