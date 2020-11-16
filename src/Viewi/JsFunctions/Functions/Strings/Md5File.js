function md5_file (str_filename) { 
  var fileGetContents = require('../filesystem/file_get_contents')
  var md5 = require('../strings/md5')
  var buf = fileGetContents(str_filename)
  if (buf === false) {
    return false
  }
  return md5(buf)
}
