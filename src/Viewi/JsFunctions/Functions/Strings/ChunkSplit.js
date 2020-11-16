function chunk_split (body, chunklen, end) { 
  chunklen = parseInt(chunklen, 10) || 76
  end = end || '\r\n'
  if (chunklen < 1) {
    return false
  }
  return body.match(new RegExp('.{0,' + chunklen + '}', 'g'))
    .join(end)
}
