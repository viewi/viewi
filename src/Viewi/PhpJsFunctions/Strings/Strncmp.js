function strncmp(str1, str2, len) {
  //  discuss at: https://www.php.net/manual/en/function.strncmp.php
  // Compares the first len characters. Like PHP, a mismatch returns the difference of the two
  // character codes (strncmp('abcd', 'abcf', 4) is -2), otherwise the difference in length.
  str1 = _phpCastString(str1).slice(0, len)
  str2 = _phpCastString(str2).slice(0, len)
  const n = Math.min(str1.length, str2.length)
  for (let i = 0; i < n; i++) {
    if (str1[i] !== str2[i]) {
      return str1.charCodeAt(i) - str2.charCodeAt(i)
    }
  }
  return str1.length - str2.length
}
