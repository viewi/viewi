function md5_file (str_filename) { 
  var fileGetContents = file_get_contents
  var md5 = md5
  var buf = fileGetContents(str_filename)
  if (buf === false) {
    return false
  }
  return md5(buf)
}
