function realpath (path) {
  if (typeof window === 'undefined') {
    var nodePath = window.path
    return nodePath.normalize(path)
  }
  var p = 0
  var arr = [] 
  var r = this.window.location.href 
  path = (path + '').replace('\\', '/')
  if (path.indexOf('://') !== -1) {
    p = 1
  }
  if (!p) {
    path = r.substring(0, r.lastIndexOf('/') + 1) + path
  }
  arr = path.split('/') 
  path = [] 
  for (var k in arr) { 
    if (arr[k] === '.') {
      continue
    }
    if (arr[k] === '..') {
      if (path.length > 3) {
        path.pop()
      }
    } else {
      if ((path.length < 2) || (arr[k] !== '')) {
        path.push(arr[k])
      }
    }
  }
  return path.join('/')
}
