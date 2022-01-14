function pathinfo (path, options) {
  var basename = window.basename
  var opt = ''
  var realOpt = ''
  var optName = ''
  var optTemp = 0
  var tmpArr = {}
  var cnt = 0
  var i = 0
  var haveBasename = false
  var haveExtension = false
  var haveFilename = false
  if (!path) {
    return false
  }
  if (!options) {
    options = 'PATHINFO_ALL'
  }
  var OPTS = {
    'PATHINFO_DIRNAME': 1,
    'PATHINFO_BASENAME': 2,
    'PATHINFO_EXTENSION': 4,
    'PATHINFO_FILENAME': 8,
    'PATHINFO_ALL': 0
  }
  for (optName in OPTS) {
    if (OPTS.hasOwnProperty(optName)) {
      OPTS.PATHINFO_ALL = OPTS.PATHINFO_ALL | OPTS[optName]
    }
  }
  if (typeof options !== 'number') {
    options = [].concat(options)
    for (i = 0; i < options.length; i++) {
      if (OPTS[options[i]]) {
        optTemp = optTemp | OPTS[options[i]]
      }
    }
    options = optTemp
  }
  var _getExt = function (path) {
    var str = path + ''
    var dotP = str.lastIndexOf('.') + 1
    return !dotP ? false : dotP !== str.length ? str.substr(dotP) : ''
  }
  if (options & OPTS.PATHINFO_DIRNAME) {
    var dirName = path
      .replace(/\\/g, '/')
      .replace(/\/[^/]*\/?$/, '') 
    tmpArr.dirname = dirName === path ? '.' : dirName
  }
  if (options & OPTS.PATHINFO_BASENAME) {
    if (haveBasename === false) {
      haveBasename = basename(path)
    }
    tmpArr.basename = haveBasename
  }
  if (options & OPTS.PATHINFO_EXTENSION) {
    if (haveBasename === false) {
      haveBasename = basename(path)
    }
    if (haveExtension === false) {
      haveExtension = _getExt(haveBasename)
    }
    if (haveExtension !== false) {
      tmpArr.extension = haveExtension
    }
  }
  if (options & OPTS.PATHINFO_FILENAME) {
    if (haveBasename === false) {
      haveBasename = basename(path)
    }
    if (haveExtension === false) {
      haveExtension = _getExt(haveBasename)
    }
    if (haveFilename === false) {
      haveFilename = haveBasename.slice(0, haveBasename.length - (haveExtension
        ? haveExtension.length + 1
        : haveExtension === false
          ? 0
          : 1
        )
      )
    }
    tmpArr.filename = haveFilename
  }
  cnt = 0
  for (opt in tmpArr) {
    if (tmpArr.hasOwnProperty(opt)) {
      cnt++
      realOpt = opt
    }
  }
  if (cnt === 1) {
    return tmpArr[realOpt]
  }
  return tmpArr
}
