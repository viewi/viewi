function dirname (path) {
  return path.replace(/\\/g, '/')
    .replace(/\/[^/]*\/?$/, '')
}
