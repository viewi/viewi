/**
 * Shake unused css in the browser
 */
var cssTreeShaker = function () {
    var cssTokens = [];
    var currentIndex = -1;
    var i = 0;
    var total = 0;
    var selector = '';
    var css = '';

    var reset = function () {
        i = 0;
        total = css.length;
        selector = '';
        currentIndex = -1;
        cssTokens = [];
    };

    var newTokensGroup = function (name) {
        name = name || 'global';
        currentIndex++;
        cssTokens.push({
            scope: name,
            rules: []
        });
    }

    var addRule = function (selector, content) {
        cssTokens[currentIndex].rules.push({
            selector: selector,
            content: content,
            valid: false
        });
    }

    var readCommentBlock = function () {
        i += 2;
        comment = '';
        while (i < total) {
            switch (css[i]) {
                case '*': {
                    if (i + 1 < total && css[i + 1] === '/') {
                        addRule('##comment##', comment);
                        i += 2;
                        return;
                    }
                }
                default: {
                    comment += css[i];
                    break;
                }
            }
            i++;
        }
    }

    var readRuleContentBlock = function () {
        i++;
        var content = '';
        var nestedCount = 0;
        while (i < total) {
            switch (css[i]) {
                case '}': {
                    if (nestedCount == 0) {
                        addRule(selector, content);
                        i += 1;
                        return;
                    } else {
                        nestedCount--;
                    }
                    content += css[i];
                    break;
                }
                case '{': {
                    nestedCount++;
                }
                default: {
                    content += css[i];
                    break;
                }
            }
            i++;
        }
    }

    var readGroupName = function () {
        var group = '';
        while (i < total) {
            switch (css[i]) {
                case '{': {
                    return group;
                }
                default: {
                    group += css[i];
                    break;
                }
            }
            i++;
        }
    }

    var shakeCssContent = function () {
        while (i < total) {
            switch (css[i]) {
                case '/': {
                    if (i + 1 < total && css[i + 1] === '*') {
                        // comment /*
                        readCommentBlock();
                        break;
                    }
                }
                case '@': {
                    // new group, ex: @media, @animation, ..
                    var group = readGroupName();
                    newTokensGroup(group);
                    selector = '';
                    break;
                }
                case '{': {
                    // rule content
                    readRuleContentBlock();
                    selector = '';
                    break;
                }
                case '}': {
                    newTokensGroup();
                    selector = '';
                    break;
                }
                default: {
                    selector += css[i];
                    break;
                }
            }
            i++;
        }
    }

    var testSelectors = function (validator) {
        for (var t = 0; t < cssTokens.length; t++) {
            var group = cssTokens[t];
            group.valid = false;
            var groupName = group.scope.replace(/^\s+|\s+$/g, '');
            for (var r = 0; r < group.rules.length; r++) {
                var rule = group.rules[r];
                var selector = rule.selector.replace(/^\s+|\s+$/g, '');
                var selectors = selector.split(',');
                for (var s = 0; s < selectors.length; s++) {
                    var subSelector = selectors[s].replace(/^\s+|\s+$/g, '');
                    var specialPos = subSelector.indexOf(':');
                    if (specialPos !== -1) {
                        subSelector = subSelector.slice(0, specialPos);
                    }
                    selectors[s] = subSelector;
                    rule.valid = rule.valid || validator(subSelector);
                }
                rule.selectors = selectors;
                group.valid = group.valid || rule.valid;
                // if(!rule.valid){
                //     console.count('not valid');
                // }else{
                //     console.count('valid');
                // }
            }
        }
    }

    this.testDocument = function (validator) {
        testSelectors(validator);
    }

    this.shake = function (cssText) {
        css = cssText;
        reset();
        newTokensGroup();
        shakeCssContent();
        return cssTokens;
    };

    this.getTree = function () {
        return cssTokens;
    }

    this.getShakedCss = function () {
        var textCss = '';
        var indentation = '  ';
        for (var t = 0; t < cssTokens.length; t++) {
            var group = cssTokens[t];
            if (group.valid) {
                var groupName = group.scope.replace(/^\s+|\s+$/g, '');
                var blocked = groupName !== 'global';
                if (blocked) {
                    textCss += groupName + ' {\n';
                }
                for (var r = 0; r < group.rules.length; r++) {
                    var rule = group.rules[r];
                    var selector = rule.selector.replace(/^\s+|\s+$/g, '');
                    if (rule.valid) {
                        textCss += (blocked ? indentation : '') + selector + ' {\n';
                        textCss += indentation + (blocked ? indentation : '') + rule.content.replace(/^\s+|\s+$/g, '');
                        textCss += blocked ? '\n' + indentation + '}\n' : '\n}\n\n';
                    }
                }
                if (blocked) {
                    textCss += '}\n\n';
                }
            }
        }
        return textCss;
    }
};

// testing in browser, next is not ES5 compatible
fetch('/css/main.dev.css').then((r) => {
    return r.text();
}).then((t) => {
    var shaker = new cssTreeShaker();
    var tokens = shaker.shake(t);
    return shaker;
}).then(function (shaker) {
    shaker.testDocument(function (selector) {
        if (selector === '##comment##') {
            return false;
        }
        try {
            return !!document.querySelector(selector);
        } catch (ex) {
            return true;
        }
    });
    window.tscss = shaker;
    console.log(shaker);
    return shaker;
});