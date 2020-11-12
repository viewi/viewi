function implode(glue, pieces) {
    var i = '';
    var retVal = '';
    var tGlue = '';

    if (arguments.length === 1) {
        pieces = glue;
        glue = '';
    }

    if (typeof pieces === 'object') {
        if (Array.isArray(pieces)) {
            return pieces.join(glue);
        }
        for (i in pieces) {
            retVal += tGlue + pieces[i];
            tGlue = glue;
        }
        return retVal;
    }

    return pieces;
}