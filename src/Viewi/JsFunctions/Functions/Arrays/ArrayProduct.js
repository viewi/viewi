function array_product (input) { 
  var idx = 0
  var product = 1
  var il = 0
  if (Object.prototype.toString.call(input) !== '[object Array]') {
    return null
  }
  il = input.length
  while (idx < il) {
    product *= (!isNaN(input[idx]) ? input[idx] : 0)
    idx++
  }
  return product
}
