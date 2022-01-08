function file_get_contents (url, flags, context, offset, maxLen) { 
  var fs = fs
  return fs.readFileSync(url, 'utf-8')
}
