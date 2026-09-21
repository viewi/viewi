function ord(string) {
  //  discuss at: https://www.php.net/manual/en/function.ord.php
  // The first character's code; 0 for ''. PHP reads a byte, so non-ASCII differs (bytes-vs-chars).
  string = _phpCastString(string)
  return string === '' ? 0 : string.charCodeAt(0)
}
