function long2ip (ip) {
  if (!isFinite(ip)) {
    return false
  }
  return [ip >>> 24 & 0xFF, ip >>> 16 & 0xFF, ip >>> 8 & 0xFF, ip & 0xFF].join('.')
}
