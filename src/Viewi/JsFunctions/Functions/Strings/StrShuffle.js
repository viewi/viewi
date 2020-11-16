function str_shuffle (str) { 
  if (arguments.length === 0) {
    throw new Error('Wrong parameter count for str_shuffle()')
  }
  if (str === null) {
    return ''
  }
  str += ''
  var newStr = ''
  var rand
  var i = str.length
  while (i) {
    rand = Math.floor(Math.random() * i)
    newStr += str.charAt(rand)
    str = str.substring(0, rand) + str.substr(rand + 1)
    i--
  }
  return newStr
}
