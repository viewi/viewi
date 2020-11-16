function microtime (getAsFloat) {
  var s
  var now
  if (typeof performance !== 'undefined' && performance.now) {
    now = (performance.now() + performance.timing.navigationStart) / 1e3
    if (getAsFloat) {
      return now
    }
    s = now | 0
    return (Math.round((now - s) * 1e6) / 1e6) + ' ' + s
  } else {
    now = (Date.now ? Date.now() : new Date().getTime()) / 1e3
    if (getAsFloat) {
      return now
    }
    s = now | 0
    return (Math.round((now - s) * 1e3) / 1e3) + ' ' + s
  }
}
