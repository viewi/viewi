function array_rand (array, num) { 
  var keys = Object.keys(array)
  if (typeof num === 'undefined' || num === null) {
    num = 1
  } else {
    num = +num
  }
  if (isNaN(num) || num < 1 || num > keys.length) {
    return null
  }
  for (var i = keys.length - 1; i > 0; i--) {
    var j = Math.floor(Math.random() * (i + 1)) 
    var tmp = keys[j]
    keys[j] = keys[i]
    keys[i] = tmp
  }
  return num === 1 ? keys[0] : keys.slice(0, num)
}
