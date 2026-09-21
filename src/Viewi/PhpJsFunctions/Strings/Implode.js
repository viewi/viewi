function implode(glue, pieces) {
  //  discuss at: https://www.php.net/manual/en/function.implode.php
  // Pieces become strings the way PHP casts them: true → '1', false/null → '', arrays → 'Array'.
  if (pieces === undefined) {
    pieces = glue
    glue = ''
  }
  return _php_array_entries(pieces).map(function (entry) {
    return _phpCastString(entry[1])
  }).join(_phpCastString(glue))
}
