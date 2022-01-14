function md5_file (str_filename) { 
  var fileGetContents = window.file_get_contents
  var md5 = window.md5
  var buf = fileGetContents(str_filename)
  if (buf === false) {
    return false
  }
  return md5(buf)
}
