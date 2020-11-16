function array_fill (startIndex, num, mixedVal) { 
  var key
  var tmpArr = {}
  if (!isNaN(startIndex) && !isNaN(num)) {
    for (key = 0; key < num; key++) {
      tmpArr[(key + startIndex)] = mixedVal
    }
  }
  return tmpArr
}
