Object.defineProperty(Array.prototype, 'first', {
    enumerable: false,
    value: function (x, g) {
        for (var k in this) {
            if (x) {
                if (x(this[k], k)) {
                    if (g) {
                        return [
                            this[k],
                            k
                        ];
                    }
                    return this[k];
                }
            } else {
                return this[k];
            }
        }
        return null;
    }
});
Object.defineProperty(Array.prototype, 'where', {
    enumerable: false,
    value: function (x) {
        var result = [];
        for (var k in this) {
            if (x(this[k], k)) {
                result.push(this[k]);
            }
        }
        return result;
    }
});
Object.defineProperty(Array.prototype, 'select', {
    enumerable: false,
    value: function (x) {
        var result = [];
        for (var k in this) {
            result.push(x(this[k], k));
        }
        return result;
    }
});
Object.defineProperty(Array.prototype, 'each', {
    enumerable: false,
    value: function (x) {
        for (var k in this) {
            x(this[k], k);
        }
        return this;
    }
});