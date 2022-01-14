function file_get_contents (url, flags, context, offset, maxLen) { 
  var fs = window.fs
  return fs.readFileSync(url, 'utf-8')
}
