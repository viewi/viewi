function in_array(needle, haystack, strict) {
    var nonStrict = !strict;
    for (var i in haystack) {
        if (
            haystack[i] === needle
            || (nonStrict && haystack[i] == needle)
        ) {
            return true;
        }
    }
    return false;
}