function realpath (path) {
  //  discuss at: https://locutus.io/php/realpath/
  // original by: mk.keck
  // improved by: Kevin van Zonneveld (https://kvz.io)
  //      note 1: Returned path is an url like e.g. 'https://yourhost.tld/path/'
  //   example 1: realpath('some/dir/.././_supporters/pj_test_supportfile_1.htm')
  //   returns 1: 'some/_supporters/pj_test_supportfile_1.htm'

  if (typeof window === 'undefined') {

    return nodePath.normalize(path)
  }

  let p = 0
  let arr = [] // Save the root, if not given
  const r = this.window.location.href // Avoid input failures

  // Check if there's a port in path (like 'https://')
  path = (path + '').replace('\\', '/')
  if (path.indexOf('://') !== -1) {
    p = 1
  }

  // Ok, there's not a port in path, so let's take the root
  if (!p) {
    path = r.substring(0, r.lastIndexOf('/') + 1) + path
  }

  // Explode the given path into it's parts
  arr = path.split('/') // The path is an array now
  path = [] // Foreach part make a check
  for (const k in arr) { // This is'nt really interesting
    if (arr[k] === '.') {
      continue
    }
    // This reduces the realpath
    if (arr[k] === '..') {
      /* But only if there more than 3 parts in the path-array.
       * The first three parts are for the uri */
      if (path.length > 3) {
        path.pop()
      }
    } else {
      // This adds parts to the realpath
      // But only if the part is not empty or the uri
      // (the first three parts ar needed) was not
      // saved
      if ((path.length < 2) || (arr[k] !== '')) {
        path.push(arr[k])
      }
    }
  }

  // Returns the absloute path as a string
  return path.join('/')
}
