function sha1_file (str_filename) { 
  var fileGetContents = window.file_get_contents
  var sha1 = window.sha1
  var buf = fileGetContents(str_filename)
  if (buf === false) {
    return false
  }
  return sha1(buf)
}
