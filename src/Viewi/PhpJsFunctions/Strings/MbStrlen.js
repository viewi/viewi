function mb_strlen(str) { // eslint-disable-line camelcase
  //  discuss at: https://www.php.net/manual/en/function.mb-strlen.php
  // Counts characters (code points): an emoji is 1, as in PHP, not the 2 UTF-16 units JS sees.
  return Array.from(_phpCastString(str)).length
}
