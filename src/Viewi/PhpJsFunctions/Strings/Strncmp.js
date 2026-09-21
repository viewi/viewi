function strncmp(str1, str2, len) {
  //  discuss at: https://www.php.net/manual/en/function.strncmp.php
  // Compares the first len characters; -1, 0 or 1, as PHP 8.2+ documents.
  str1 = _phpCastString(str1).slice(0, len)
  str2 = _phpCastString(str2).slice(0, len)
  const n = Math.min(str1.length, str2.length)
  for (let i = 0; i < n; i++) {
    if (str1[i] !== str2[i]) {
      return str1.charCodeAt(i) < str2.charCodeAt(i) ? -1 : 1
    }
  }
  return Math.sign(str1.length - str2.length)
}
