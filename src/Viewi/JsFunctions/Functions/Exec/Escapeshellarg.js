function escapeshellarg (arg) {
  if(arg.indexOf("\x00") !== -1) {
    throw new Error('escapeshellarg(): Argument #1 ($arg) must not contain any null bytes');
  }
  var ret = ''
  ret = arg.replace(/\'/g, '\'\\\'\'')
  return "'" + ret + "'"
}
