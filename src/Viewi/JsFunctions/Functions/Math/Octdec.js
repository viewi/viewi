function octdec (octString) {
  octString = (octString + '').replace(/[^0-7]/gi, '')
  return parseInt(octString, 8)
}
