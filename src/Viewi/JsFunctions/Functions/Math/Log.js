function log (arg, base) {
  return (typeof base === 'undefined')
    ? Math.log(arg)
    : Math.log(arg) / Math.log(base)
}
