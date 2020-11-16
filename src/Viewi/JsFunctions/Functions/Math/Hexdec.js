function hexdec (hexString) {
  hexString = (hexString + '').replace(/[^a-f0-9]/gi, '')
  return parseInt(hexString, 16)
}
