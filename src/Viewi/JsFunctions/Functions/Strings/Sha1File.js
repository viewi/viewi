function sha1_file (str_filename) { 
  var fileGetContents = require('../filesystem/file_get_contents')
  var sha1 = require('../strings/sha1')
  var buf = fileGetContents(str_filename)
  if (buf === false) {
    return false
  }
  return sha1(buf)
}
