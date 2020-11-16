function array_sum (array) { 
  var key
  var sum = 0
  if (typeof array !== 'object') {
    return null
  }
  for (key in array) {
    if (!isNaN(parseFloat(array[key]))) {
      sum += parseFloat(array[key])
    }
  }
  return sum
}
