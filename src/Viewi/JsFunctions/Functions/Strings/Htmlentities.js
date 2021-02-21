function htmlentities (string, quoteStyle, charset, doubleEncode) {
  var div = document.createElement("div");
  div.textContent = string || '';
  return div.innerHTML || '';
}
