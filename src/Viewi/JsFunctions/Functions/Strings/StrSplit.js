function str_split (string, splitLength) { 
  if (!splitLength) {
    splitLength = 1
  }
  if (string === null || splitLength < 1) {
    return false
  }
  string += ''
  var chunks = []
  var pos = 0
  var len = string.length
  while (pos < len) {
    chunks.push(string.slice(pos, pos += splitLength))
  }
  return chunks
}
