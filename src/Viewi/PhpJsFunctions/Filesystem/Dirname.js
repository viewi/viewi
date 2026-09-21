function dirname(path, levels) {
  //  discuss at: https://www.php.net/manual/en/function.dirname.php
  // PHP's rules for '/' paths: dirname('/etc/') is '/', dirname('file.txt') is '.', and levels
  // climbs that many parents.
  path = _phpCastString(path)
  levels = levels === undefined ? 1 : _php_cast_int(levels)
  for (let i = 0; i < levels; i++) {
    const trimmed = path.replace(/\/+$/, '')
    if (trimmed === '') {
      return path === '' ? '' : '/'
    }
    const slash = trimmed.lastIndexOf('/')
    if (slash === -1) {
      return '.'
    }
    path = trimmed.slice(0, slash).replace(/\/+$/, '') || '/'
    if (path === '/') {
      return '/'
    }
  }
  return path
}
