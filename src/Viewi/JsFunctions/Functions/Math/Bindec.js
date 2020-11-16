function bindec (binaryString) {
  binaryString = (binaryString + '').replace(/[^01]/gi, '')
  return parseInt(binaryString, 2)
}
