console.log('app.js included');
// load /public/app/build/components.json
function OnReady(func) {
    var $this = this;
    this.then = function (onOk) {
        this.onOk = onOk;
    };
    this.catch = function (onError) {
        this.onError = onError;
    };
    func(function (data) {
        $this.onOk(data);
    }, function () {
        $this.onError();
    }
    );
}
var ajax = {
    get: function (url) {
        return new OnReady(function (onOk, onError) {
            var req = new XMLHttpRequest();
            req.onreadystatechange = function () {
                if (req.readyState === 4) {
                    var status = req.status;
                    if (status === 0 || (status >= 200 && status < 400)) {
                        var contentType = req.getResponseHeader("Content-Type");
                        if (contentType === 'application/json') {
                            onOk(JSON.parse(req.responseText));
                        } else {
                            onOk(req.responseText);
                        }
                    } else {
                        onError();
                    }
                }
            }
            req.open('GET', url, true);
            req.send();
        });
    }
};
Object.defineProperty(Array.prototype, 'first', {
    enumerable: false,
    value: function (x) {
        for (var k in this) {
            if (x) {
                if (x(this[k])) {
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
            if (x(this[k])) {
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
            result.push(x(this[k]));
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
//edgeon
//quicks
//
function Edgeon() {
    var $this = this;
    var avaliableTags = {};
    this.componentsUrl = '/public/build/components.json';
    this.components = {};
    this.start = function () {
        ajax.get(this.componentsUrl)
            .then(function (components) {
                $this.components = components;
                components._meta.tags.split(',').each(function (x) {
                    avaliableTags[x] = true;
                });
                $this.render('HomePage');
            });
    };

    var currentComponent = null;
    var currentScope = [];

    var getDataExpression = function (item, instance) {
        // Function.apply(null, ['a', 'return a;'])
        var itsEvent = arguments.length > 2 && arguments[2];
        var forceRaw = arguments.length > 3 && arguments[3];
        if (item.expression) {
            var contentExpression = {
                call: true,
                instance: instance
            };
            var args = ['_component', 'app'];
            if (itsEvent) {
                args.push('event');
            }
            args = args.concat(currentScope)
            if (item.raw || forceRaw) {
                args.push('return ' + item.code + ';');
            } else {
                args.push('return app.htmlentities(' + item.code + ');');
            }
            contentExpression.func = Function.apply(null, args);
            return contentExpression;
        }
        return { call: false, content: item.content };
    }

    var specialTags = ['template'];
    var specialTypes = ['if', 'else-if', 'else', 'foreach'];
    var conditionalTypes = ['if', 'else-if', 'else'];
    var requirePreviousIfTypes = ['else-if', 'else'];
    var usedSpecialTypes = [];

    var build = function (parent, instance) {
        var stack = arguments.length > 2 ? arguments[2] : false;
        var parentNode = arguments.length > 3 ? arguments[3] : null;
        var children = parent.children;
        var currentNodeList = [];
        var skip = false;
        var node = false;
        var previousNode = null;
        var usedSubscriptions = {};
        for (var i in children) {
            var item = children[i];

            if (item.type === 'tag' && item.content === 'slot') {
                // if (currentNodeList.length > 0) {
                //     currentNodeList[currentNodeList.length - 1].nextNode = null;
                //     currentNodeList[currentNodeList.length - 1].previousNode =
                //         currentNodeList.length > 1
                //             ? currentNodeList[currentNodeList.length - 2]
                //             : null;
                // }

                skip = true;
                var slotNameItem = item.attributes && item.attributes.first(function (x) { return x.content === 'name'; });
                var slotName = 0;
                var slotNameExpression = function (x) {
                    return !x.attributes;
                };
                if (slotNameItem) {
                    slotName = slotNameItem.children[0].content;
                    slotNameExpression = function (x) {
                        return x.attributes
                            && x.attributes.first(function (y) {
                                return y.content === 'name'
                                    && y.children[0].content === slotName;
                            });
                    }
                }
                if (stack) {
                    if (slotName === 0) {
                        var items = stack.where(function (x) {
                            return x.type !== 'tag' && x.contents[0].content !== 'slotContent';
                        });
                        // reassign parent
                        var prevNode = currentNodeList.length > 0
                            ? currentNodeList[currentNodeList.length - 1]
                            : null;
                        var toConcat = [];
                        items.each(function (x) {
                            if (prevNode
                                && prevNode.type === 'text'
                                && x.type === 'text'
                                && !x.raw
                                && !prevNode.raw
                            ) {
                                prevNode.contents = prevNode.contents.concat(x.contents);
                            } else {
                                x.nextNode = null;
                                x.parent = parentNode;
                                x.previousNode = prevNode;
                                if (prevNode) {
                                    prevNode.nextNode = x;
                                }
                                prevNode = x;
                                toConcat.push(x);
                            }
                        });
                        currentNodeList = currentNodeList.concat(toConcat);
                    } else {
                        var slotContent = stack.first(function (x) {
                            return x.type === 'tag'
                                && x.contents[0].content === 'slotContent'
                                && slotNameExpression(x);
                        });
                        if (slotContent) {
                            // reassign parent
                            var prevNode = currentNodeList.length > 0
                                ? currentNodeList[currentNodeList.length - 1]
                                : null;
                            var toConcat = [];
                            slotContent.children.each(function (x) {
                                if (prevNode
                                    && prevNode.type === 'text'
                                    && x.type === 'text'
                                    && !x.raw
                                    && !prevNode.raw
                                ) {
                                    prevNode.contents = prevNode.contents.concat(x.contents);
                                } else {
                                    x.nextNode = null;
                                    x.parent = parentNode;
                                    x.previousNode = prevNode;
                                    if (prevNode) {
                                        prevNode.nextNode = x;
                                    }
                                    prevNode = x;
                                    toConcat.push(x);
                                }
                            });
                            currentNodeList = currentNodeList.concat(toConcat);
                        }
                    }
                    previousNode = currentNodeList.length > 0
                        ? currentNodeList[currentNodeList.length - 1]
                        : null;
                } else {
                    // unnamed slot
                    var defaultContent = build(item, instance, false, parentNode);
                    // reassign parent
                    var prevNode = currentNodeList.length > 0
                        ? currentNodeList[currentNodeList.length - 1]
                        : null;
                    var toConcat = [];
                    defaultContent.each(function (x) {
                        if (prevNode
                            && prevNode.type === 'text'
                            && x.type === 'text'
                            && !x.raw
                            && !prevNode.raw
                        ) {
                            prevNode.contents = prevNode.contents.concat(x.contents);
                        } else {
                            x.nextNode = null;
                            x.parent = parentNode;
                            x.previousNode = prevNode;
                            if (prevNode) {
                                prevNode.nextNode = x;
                            }
                            prevNode = x;
                            toConcat.push(x);
                        }
                    });
                    currentNodeList = currentNodeList.concat(toConcat);
                    previousNode = currentNodeList.length > 0
                        ? currentNodeList[currentNodeList.length - 1]
                        : null;
                }
                continue;
            }

            if (!item.raw && node && (item.type === 'text' && node.type === 'text')
                || (item.type === 'comment' && node.type === 'comment')
            ) {
                node.contents.push(getDataExpression(item, instance));
                if (item.subs) {
                    for (var s in item.subs) {
                        listenTo(node, item.subs[s]);
                    }
                }
                continue;
            }
            var specialType = null;
            if (item.attributes) {
                specialType = item.attributes.first(function (a) {
                    return specialTypes.indexOf(a.content) !== -1 && usedSpecialTypes.indexOf(a.content) === -1;
                });
                //.select(function (a) { return a.content; }).first();
            }
            var component = false;
            node = {
                // TODO: process foreach
                type: item.type,
                contents: [getDataExpression(item, instance)],
                domNode: null, // DOM node if rendered
                // conditions: [true], // collection of expressions (if,else,elseif)
                parent: parentNode, // TODO: make imutable
                instance: instance,
                previousNode: previousNode
            };
            if (item.raw) {
                node.type = 'raw';
                node.isVirtual = true;
            }
            if (parentNode && parentNode.condition) {
                node.condition = parentNode.condition;
            }
            if (previousNode) {
                previousNode.nextNode = node;
            }
            previousNode = node;
            if (item.type === 'tag' && item.expression) {
                node.type = 'dynamic';
                node.componentChilds = item.children;
                node.isVirtual = true;
            }
            if (specialType === null && item.type === 'tag' && specialTags.indexOf(item.content) !== -1) {
                var specialTag = item.content;
                node.type = specialTag;
                node.isVirtual = true;
            }
            if (specialType !== null) {
                node.type = specialType.content;
                if (node.type === 'if') { // reset group
                    usedSubscriptions = {};
                }
                node.isVirtual = true;
                usedSpecialTypes.push(specialType.content);
                if (conditionalTypes.indexOf(node.type) !== -1) {
                    node.condition = specialType.children
                        ? getDataExpression(specialType.children[0], instance)
                        : {};
                    for (var s in usedSubscriptions) {
                        listenTo(node, s);
                    }
                    if (specialType.children && specialType.children[0].subs) { // TOD: subscribe all if-else group to each sub changes
                        for (var s in specialType.children[0].subs) {
                            listenTo(node, specialType.children[0].subs[s]);
                            usedSubscriptions[specialType.children[0].subs[s]] = true;
                        }
                    }
                }
                var codeChild = false;
                if (node.type === 'foreach') {
                    // compile foreach expression
                    if (specialType.children) {
                        codeChild = specialType.children[0];
                        for (var s in codeChild.subs) {
                            listenTo(node, codeChild.subs[s]);
                        }
                        node.forExpression = {};
                        var arguments = ['_component', 'app'].concat(currentScope);
                        arguments.push('return ' + codeChild.forData + ';');
                        node.forExpression.data = Function.apply(null, arguments);
                        node.forExpression.key = codeChild.forKey;
                        node.forExpression.value = codeChild.forItem;
                        currentScope.push(codeChild.forKey);
                        currentScope.push(codeChild.forItem);
                        node.scope = {
                            stack: currentScope.slice(),
                            data: {}
                        }
                        // console.log(node, node.forExpression, currentScope);
                    }
                }
                node.children = build({ children: [item] }, instance, stack, node);
                // reset currentScope
                if (codeChild) {
                    // remove from currentScope
                    var remIn = currentScope.indexOf(codeChild.forKey);
                    if (remIn > -1) {
                        currentScope.splice(remIn, 1);
                    }
                    remIn = currentScope.indexOf(codeChild.forItem);
                    if (remIn > -1) {
                        currentScope.splice(remIn, 1);
                    }
                }

                currentNodeList.push(node);
                // TODO: subscribe
                // TODO: create check function
                continue;
            } else if (usedSpecialTypes.length > 0) {
                usedSpecialTypes = [];
            }
            if (item.type === 'component') {
                component = item.content;
            }
            if (item.subs) {
                for (var s in item.subs) {
                    listenTo(node, item.subs[s]);
                }
            }
            // children
            childNodes = false;
            if (item.children) {
                childNodes = build(item, instance, stack, node);
            }
            if (item.attributes) {
                node.attributes = item.attributes
                    .where(function (a) {
                        return specialTypes.indexOf(a.content) === -1;
                    })
                    .select(
                        function (a) {
                            var copy = {};
                            var itsEvent = a.content[0] === '(';
                            copy.content = a.content; // keep it for slots
                            copy.isAttribute = true;
                            copy.parent = node;
                            copy.contentExpression = getDataExpression(a, instance);
                            copy.instance = node.instance;
                            if (node.scope) {
                                copy.scope = node.scope;
                            }
                            if (a.children) {
                                copy.children = a.children.select(
                                    function (v) {
                                        var valCopy = {};
                                        valCopy.contentExpression = getDataExpression(v, instance, itsEvent);
                                        if (node.type === 'dynamic'
                                            || node.type === 'component'
                                        ) { // we need props
                                            valCopy.propExpression = getDataExpression(v, instance, null, true);
                                        }

                                        valCopy.content = v.content; // keep it for slots
                                        if (v.subs && !itsEvent) {
                                            for (var s in v.subs) {
                                                listenTo(copy, v.subs[s]);
                                            }
                                        }
                                        return valCopy;
                                    }
                                );
                            }
                            if (a.subs && !itsEvent) {
                                for (var s in a.subs) {
                                    listenTo(copy, a.subs[s]);
                                }
                            }
                            return copy;
                        }
                    );
            }
            if (component) {
                // TODO: reassign parent, next, previous ???
                var componenNodes = create(component, childNodes, node.attributes);
                // componenNodes.each(function (x) {
                //     x.parent = node;
                // });

                var prevNode = currentNodeList.length > 0
                    ? currentNodeList[currentNodeList.length - 1]
                    : null;
                var toConcat = [];
                componenNodes.each(function (x) {
                    if (prevNode
                        && prevNode.type === 'text'
                        && x.type === 'text'
                        && !x.raw
                        && !prevNode.raw
                    ) {
                        prevNode.contents = prevNode.contents.concat(x.contents);
                    } else {
                        x.nextNode = null;
                        x.parent = parentNode;
                        x.previousNode = prevNode;
                        if (prevNode) {
                            prevNode.nextNode = x;
                        }
                        prevNode = x;
                        toConcat.push(x);
                    }
                });
                currentNodeList = currentNodeList.concat(toConcat);
                previousNode = currentNodeList.length > 0
                    ? currentNodeList[currentNodeList.length - 1]
                    : null;
                // if (currentNodeList.length > 0
                //     && componenNodes.length > 0) {
                //     componenNodes[0].previousNode = currentNodeList[currentNodeList.length - 1];
                //     currentNodeList[currentNodeList.length - 1].nextNode = componenNodes[0];
                // }
                // currentNodeList = currentNodeList.concat(componenNodes);
            } else {
                if (childNodes) {
                    if (node.type === 'dynamic' || node.type === 'raw') {
                        node.itemChilds = childNodes;
                    } else {
                        node.children = childNodes;
                    }
                }
                currentNodeList.push(node);
            }
        }
        return currentNodeList;
    };

    var renderAttribute = function (elm, attr) {
        if (!elm) {
            return;
        }
        try {
            if (attr.content[0] === '(') { // TODO: attach event only once
                var eventName = attr.content.substring(1, attr.content.length - 1);
                var actionContent = attr.children[0].contentExpression.func;
                // console.log(elm, eventName, attr.children); // TODO: attach event data $event
                elm.addEventListener(eventName, function ($event) {
                    actionContent(attr.parent.instance, $this, $event);
                });
            } else {
                // TODO: dynamic attribute name
                var texts = [];
                for (var i in attr.children) {
                    var contentExpression = attr.children[i].contentExpression;
                    if (contentExpression.call) {
                        var args = [attr.instance, $this];
                        if (attr.scope) {
                            for (var k in attr.scope.stack) {
                                args.push(attr.scope.data[attr.scope.stack[k]]);
                            }
                        }
                        texts.push(contentExpression.func.apply(null, args));
                    } else {
                        texts.push(contentExpression.content);
                    }
                }
                var val = texts.join('');
                elm.setAttribute(attr.content, val);
            }
        } catch (ex) {
            console.error(ex);
            console.log(attr.content);
        }
    }

    var getFirstBefore = function (node) {
        var nodeBefore = node;
        var skipCurrent = true;
        var parent = false;
        while (!nodeBefore.domNode || skipCurrent) {
            skipCurrent = false;
            if (nodeBefore.previousNode !== null) {
                nodeBefore = nodeBefore.previousNode;
                parent = false;
                if (nodeBefore.isVirtual) {
                    // go down
                    var potencialNode = nodeBefore;
                    while (potencialNode !== null && !potencialNode.domNode) {
                        if (potencialNode.isVirtual && potencialNode.children) {
                            potencialNode = potencialNode.children[potencialNode.children.length - 1];
                        } else {
                            potencialNode = null;
                        }
                    }
                    if (potencialNode) {
                        nodeBefore = potencialNode;
                    }
                }
            } else {
                if (nodeBefore.parent === null) {
                    return null;
                }
                nodeBefore = nodeBefore.parent;
                parent = true;
            }
        }
        return { itsParent: parent, node: nodeBefore };
    }

    var nextNodeId = 0;

    var removeDomNodes = function (nodes) {
        for (var i in nodes) {
            if (nodes[i].domNode !== null) {
                // TODO: remove children
                // TODO: on remove rerender sibilings before and after if text or virtual
                nodes[i].domNode.parentNode.removeChild(nodes[i].domNode);
                nodes[i].domNode = null;
            }
            if (nodes[i].children) {
                removeDomNodes(nodes[i].children);
            }
        }
    }

    var renderScopeStack = [];

    var createDomNode = function (parent, node, insert, skipGroup) {
        if (!skipGroup) {
            if (node.isVirtual || node.type === 'text') {
                // TODO: make property which says if text or virtual node has text or virtual sibilings
                // TODO: make property which indicates if node is text type (shortness)
                // TODO: make track property to set render version and render group once
                // render fresh DOM from first text/virtual to the last text/virtual
                // TODO: save render version and rerender only once node.v = renderVersion (update renderVersion on changes)
                // TODO: set if node needs rerender sibling on build/compile stage
                var startNode = node;
                while (startNode.parent && startNode.parent.isVirtual) {
                    startNode = startNode.parent;
                }
                var firstRealNode = getFirstBefore(node);
                var startParentDomNode = (
                    firstRealNode
                        && firstRealNode.itsParent
                        ? firstRealNode.node.domNode
                        : firstRealNode && firstRealNode.node.domNode.parentNode
                    // && firstRealNode.domNode.parentNode
                ) || parent;
                var fromNode = startNode;
                while (
                    fromNode.previousNode
                    && (fromNode.previousNode.isVirtual || fromNode.previousNode.type === 'text')
                ) {
                    fromNode = fromNode.previousNode;
                }
                var toNode = startNode;
                while (
                    toNode.nextNode
                    && (toNode.nextNode.isVirtual || toNode.nextNode.type === 'text')
                ) {
                    toNode = toNode.nextNode;
                }
                if (fromNode !== startNode || toNode !== startNode) {
                    // console.log('render from ', fromNode, ' to ', toNode);
                    var currentNode = fromNode;
                    var hasNext = true;
                    while (hasNext) {
                        createDomNode(startParentDomNode, currentNode, true, true);
                        hasNext = currentNode !== toNode;
                        // if (hasNext && !currentNode.nextNode) {
                        //     debugger;
                        // }
                        currentNode = currentNode.nextNode;
                    }
                    return;
                }
            }
        }
        if (insert) {
            // console.log(node.children[0].contents[0].content, node);
            var condition = node.parent && node.parent.condition;
            var active = condition && condition.value;
            if (condition && !active) { // remove
                removeDomNodes([node]);
                return;
            }
        }
        if (!node.isVirtual && node.condition && !node.condition.value) {
            return;
        }
        var texts = [];
        for (var i in node.contents) {
            var contentExpression = node.contents[i];
            if (contentExpression.call) {
                var args = [contentExpression.instance, $this];
                if (node.scope) {
                    for (var k in node.scope.stack) {
                        args.push(node.scope.data[node.scope.stack[k]]);
                    }
                }
                texts.push(contentExpression.func.apply(null, args));
            } else {
                texts.push(contentExpression.content);
            }
        }
        var val = texts.join(''); // TODO: process conditions (if,else,elif)
        var elm = false;
        var nextInsert = false;
        switch (node.type) {
            case 'text': { // TODO: implement text insert
                if (parent === document) {
                    elm = document.childNodes[0]; // TODO: check for <!DOCTYPE html> node
                    break;
                }
                var skip = false;
                var nodeBefore = getFirstBefore(node);
                if (insert) {
                    // look up for previous text to append or insert after first tag
                    if (nodeBefore == null) {
                        return;
                        break; // throw error ??
                    }
                }
                if (!skip) {
                    if (nodeBefore && nodeBefore.node.type == 'text') {
                        nodeBefore.node.domNode.nodeValue += val;
                        if (node.domNode !== null) {
                            node.domNode.parentNode.removeChild(node.domNode);
                            node.domNode = null;
                        }
                    } else {
                        if (node.domNode !== null && node.domNode.parentNode === parent) {
                            node.domNode.nodeValue = val;
                            elm = node.domNode;
                        } else {
                            elm = document.createTextNode(val);
                            var nextSibiling = nodeBefore.node.domNode.nextSibling;
                            if (!nodeBefore.itsParent && nextSibiling !== null) {
                                nextSibiling.parentNode.insertBefore(elm, nextSibiling);
                            } else if (nodeBefore.itsParent) {
                                nodeBefore.node.domNode.appendChild(elm);
                            } else {
                                nodeBefore.node.domNode.parentNode.appendChild(elm);
                            }
                            node.domNode = elm;
                        }
                    }
                }
                break;
            }
            case 'tag': {
                if (parent === document) {
                    elm = document.childNodes[1]; // TODO: check for html node
                    node.domNode = elm;
                    break;
                }
                if (val === 'script') {
                    return; // skip script for now, TODO: process scripts, styles
                }
                elm = document.createElement(val);
                if (node.domNode !== null) {
                    node.domNode.parentNode.replaceChild(elm, node.domNode);
                } else {
                    if (insert) {
                        // find first previous not virtual up tree non virtual
                        var nodeBefore = getFirstBefore(node);
                        if (nodeBefore == null) {
                            return;
                            break; // throw error ??
                        }
                        var nextSibiling = nodeBefore.node.domNode.nextSibling;
                        if (!nodeBefore.itsParent && nextSibiling !== null) {
                            nextSibiling.parentNode.insertBefore(elm, nextSibiling);
                        } else if (nodeBefore.itsParent) {
                            nodeBefore.node.domNode.appendChild(elm);
                        } else {
                            nodeBefore.node.domNode.parentNode.appendChild(elm);
                        }
                    } else {
                        parent.appendChild(elm);
                    }
                }
                node.domNode = elm;
                if (node.attributes) {
                    for (var a in node.attributes) {
                        renderAttribute(elm, node.attributes[a]);
                    }
                }
                break;
            }
            case 'comment': {
                elm = document.createComment(val);
                if (node.domNode !== null) {
                    node.domNode.parentNode.replaceChild(elm, node.domNode);
                } else {
                    if (insert) {
                        // find first previous not virtual up tree non virtual
                        var nodeBefore = getFirstBefore(node);
                        if (nodeBefore == null) {
                            return;
                            break; // throw error ??
                        }
                        var nextSibiling = nodeBefore.node.domNode.nextSibling;
                        if (!nodeBefore.itsParent && nextSibiling !== null) {
                            nextSibiling.parentNode.insertBefore(elm, nextSibiling);
                        } else if (nodeBefore.itsParent) {
                            nodeBefore.node.domNode.appendChild(elm);
                        } else {
                            nodeBefore.node.domNode.parentNode.appendChild(elm);
                        }
                    } else {
                        parent.appendChild(elm);
                    }
                }
                node.domNode = elm;
                break;
            }
            case 'if': {
                // TODO: check conditon
                // TODO: if false remove node if exists
                // TODO: if true create element
                node.condition.value = node.condition.func(node.instance, $this);
                elm = parent;
                node.parentDomNode = parent;
                node.condition.previousValue = node.condition.value;
                node.topRealPreviousNode = node.parent.topRealPreviousNode || node.previousNode;
                nextInsert = true;
                break;
            }
            case 'else-if': {
                // TODO: check condition
                node.condition.value = !node.previousNode.condition.value
                    && node.condition.func(node.instance, $this);
                node.condition.previousValue = node.previousNode.condition.value || node.condition.value;
                elm = parent;
                node.parentDomNode = parent;
                node.topRealPreviousNode = node.parent.topRealPreviousNode || node.previousNode;
                nextInsert = true;
                break;
            }
            case 'else': {
                // TODO: check condition
                node.condition.value = !node.previousNode.condition.previousValue;
                elm = parent;
                node.parentDomNode = parent;
                node.topRealPreviousNode = node.parent.topRealPreviousNode || node.previousNode;
                nextInsert = true;
                break;
            }
            case 'template': {
                elm = parent;
                nextInsert = true;
                break;
            }
            case 'foreach': {
                elm = parent;
                nextInsert = true;
                if (elm) {
                    // create n nodes (copy of children) and render
                    if (!node.itemChilds) { // TODO: bug, need to rewrite
                        node.itemChilds = node.children;
                    }
                    removeDomNodes(node.children);
                    node.children = null;
                    var args = [node.instance, $this];
                    // args = args.concat(currentScope); // TODO concat with scope values
                    var data = node.forExpression.data.apply(null, args);
                    if (data && data.length > 0) {
                        node.children = [];
                    }
                    var prevNode = null;
                    for (var k in data) {
                        var wrapperNode = {
                            type: 'template',
                            isVirtual: true,
                            parent: node,
                            previousNode: prevNode,
                            instance: node.instance,
                            domNode: null,
                            scope: {
                                stack: node.scope.stack,
                                data: Object.assign({}, node.scope.data)
                            }
                        };
                        if (prevNode) {
                            prevNode.nextNode = wrapperNode;
                        }
                        prevNode = wrapperNode;
                        wrapperNode.scope.data[node.forExpression.key] = k;
                        wrapperNode.scope.data[node.forExpression.value] = data[k];
                        copyNodes(wrapperNode, node.itemChilds);
                        node.children.push(wrapperNode);
                    }
                    // TODO: resubscribe for changes, remove subscriptions for itemChilds

                }
                console.log(node, data);
                break;
            }
            case 'dynamic': { // dynamic tag or component
                // wrap into template
                // clone child for tag
                // or build component
                // render
                elm = parent;
                nextInsert = true;
                removeDomNodes(node.children);
                node.children = null;
                var wrapperNode = {
                    contents: node.contents,
                    attributes: node.attributes,
                    parent: node,
                    previousNode: null,
                    scope: node.scope,
                    instance: node.instance,
                    domNode: null
                };
                if (node.itemChilds) {
                    copyNodes(wrapperNode, node.itemChilds);
                }
                if (val in avaliableTags) { // it's a tag
                    wrapperNode.type = 'tag';
                } else {
                    // build component
                    // componentChilds
                    wrapperNode.type = 'template';
                    wrapperNode.isVirtual = true;
                    wrapperNode.children = create(val, wrapperNode.children, node.attributes);
                    // reassign parent
                    wrapperNode.children.each(function (x) {
                        x.parent = wrapperNode;
                    });
                }
                node.children = [wrapperNode];
                console.log(node, val);
                break;
            }
            case 'raw': {
                elm = parent;
                nextInsert = true;
                if (node.rawNodes) {
                    removeDomNodes(node.rawNodes);
                }
                node.children = null;
                node.rawNodes = null;
                var firstParentNode = getFirstParentWithDom(node);
                if (firstParentNode) {
                    // console.log(val);
                    var vdom = document.createElement(firstParentNode.domNode.nodeName);
                    vdom.innerHTML = val;
                    // cases:
                    // 1: only text node
                    // 2: only non text node(s)
                    // 3: text,..any, text
                    // 4: text,.. any, non text
                    console.log({ d: vdom });
                    if (vdom.childNodes.length > 0) {
                        var startI = 0;
                        var firstClosest = getFirstBefore(node);
                        if (!firstClosest.itsParent
                            && firstClosest.node.type === 'text'
                            && vdom.childNodes[0].nodeType === 3
                        ) { // merge text
                            firstClosest.node.domNode.nodeValue +=
                                vdom.childNodes[0].nodeValue;
                            startI = 1;
                        }
                        var newNodes = [];

                        // insert after
                        for (startI; startI < vdom.childNodes.length; startI++) {
                            newNodes.push({
                                domNode: vdom.childNodes[startI]
                            });
                        }
                        var nextSibiling = firstClosest.node.domNode.nextSibling;
                        if (newNodes.length > 0) {
                            for (var i = 0; i < newNodes.length; i++) {
                                if (nextSibiling) {
                                    nextSibiling.parentNode.insertBefore(newNodes[i].domNode, nextSibiling);
                                } else {
                                    firstClosest.node.domNode.parentNode.appendChild(newNodes[i].domNode);
                                }
                            }
                            var latestNode = newNodes[newNodes.length - 1];
                            node.children = [{
                                type: latestNode.domNode.nodeType === 3 ? 'text' : 'tag', // non text, tag is good
                                parent: node,
                                previousNode: null,
                                scope: node.scope,
                                instance: node.instance,
                                domNode: latestNode.domNode
                            }];
                            node.rawNodes = newNodes;
                        }
                    }
                }
                return; // do not run children
            }
            default:
                throw new Error('Node type \'' + node.type + '\' is not implemented.');
        }
        elm && createDOM(elm, node.children, nextInsert, skipGroup);
    }

    var createDOM = function (parent, nodes, insert, skipGroup) {
        for (var i in nodes) {
            createDomNode(parent, nodes[i], insert, skipGroup);
        }
    }

    var getFirstParentWithDom = function (node) {
        if (!node.parent) {
            return null;
        }
        if (node.parent.domNode) {
            return node.parent;
        }
        return getFirstParentWithDom(node.parent);
    }

    var removeDomNodes = function (nodes) {
        for (var k in nodes) {
            if (nodes[k].children) {
                removeDomNodes(nodes[k].children);
            }
            if (nodes[k].domNode) {
                nodes[k].domNode.parentNode.removeChild(nodes[k].domNode);
                nodes[k].domNode = null;
            }
        }
    }

    var copyNodes = function (parent, nodes) {
        var prev = null;
        var newChildren = nodes.select(function (x) {
            var z = cloneNode(parent, x);
            z.parent = parent;
            z.previousNode = prev;
            prev = z;
            if (z.previousNode) {
                z.previousNode.nextNode = z;
            }
            return z;
        });
        parent.children = newChildren;
    }

    var cloneNode = function (parent, node) {
        var copy = Object.assign({}, node);
        copy.scope = parent.scope;
        copy.nextNode = null;
        copy.previousNode = null;
        copy.domNode = null;
        copy.scope = parent.scope;
        if (node.children) {
            copyNodes(copy, node.children)
        }
        return copy;
    }

    var nextInstanceId = 0;

    var getInstanceId = function (instance) {
        if (!('__id' in instance)) {
            Object.defineProperty(instance, "__id", {
                enumerable: false,
                writable: false,
                value: ++nextInstanceId
            });
        }
        return instance.__id;
    }

    var renderQueue = {};
    var subscribers = {};


    var listenTo = function (node, path) {
        var isAttribute = node.isAttribute;
        var instance = isAttribute ? node.parent.instance : node.instance;
        var iid = getInstanceId(instance);
        if (!(iid in subscribers)) {
            subscribers[iid] = {};
        }
        if (!(path in subscribers[iid])) {
            subscribers[iid][path] = [];
        }
        subscribers[iid][path].push(node);
    }

    // TODO: on change conditions, insert element if new, remove if not active
    var reRender = function () {
        for (var path in renderQueue) {
            for (var i in renderQueue[path]) {
                var node = renderQueue[path][i];
                if (node.isAttribute) {
                    renderAttribute(node.parent.domNode, node);
                } else if (node.isVirtual) {
                    createDomNode(node.parentDomNode, node);
                } else {
                    createDomNode(node.domNode && node.domNode.parentNode, node);
                }
            }
        }
        renderQueue = {};
    }

    var onChange = function (deps) {
        for (var key in deps.subs) {
            var dep = deps.subs[key];
            var iid = dep.id;
            if (iid in subscribers
                && dep.path in subscribers[iid]) {
                renderQueue[iid + '_' + dep.path] = subscribers[iid][dep.path];
                setTimeout(function () {
                    reRender();
                }, 0);
            }
        }
    };

    this.notify = function (obj, type) {
        if (Array.isArray(obj)) {
            var prot = Object.getPrototypeOf(obj);
            if (prot.deps) {
                onChange(prot.deps);
            }
        }
    };

    //reactivate array
    var ra = function (a, deps) {
        var p = Object.getPrototypeOf(a);
        var np = {};
        Object.defineProperty(np, "deps", {
            enumerable: false,
            writable: false,
            value: deps
        });
        Object.setPrototypeOf(np, p);
        Object.setPrototypeOf(a, np);
        return a;
    }

    var makeReactive = function (obj) {
        var instance = arguments.length > 1 ? arguments[1] : obj;
        var path = arguments.length > 2 ? arguments[2] : 'this';
        var deps = arguments.length > 3 ? arguments[3] : { subs: {} };
        if (Array.isArray(obj)) {
            // reactivate array
            // TODO: make optimization and fire local changes instead of whole array
            ra(obj, deps);
            for (var i = 0; i < obj.length; i++) {
                if (obj[i] !== null && typeof obj[i] === 'object') {
                    makeReactive(obj[i], instance, path + '[key]');
                }
            }
        } else {
            var keys = Object.keys(obj);
            for (var i = 0; i < keys.length; i++) {
                defineReactive(instance, path + '.' + keys[i], obj, keys[i]);
            }
        }
        return obj;
    };

    var latestDeps = null;

    var defineReactive = function (instance, path, obj, key) {
        latestDeps = null;
        var deps = null;
        var val = obj[key];
        var itsNew = false;
        if (latestDeps) {
            deps = latestDeps;
        } else {
            deps = { subs: {} };
            itsNew = true;
        }
        if (val !== null && typeof val === 'object') {
            makeReactive(val, instance, path, deps);
        }
        if (typeof val === 'function') { // reactive methods ???
            return;
        }
        var iid = getInstanceId(instance);
        deps.subs[iid + path] = { instance: instance, id: iid, path: path };
        if (itsNew) {
            Object.defineProperty(obj, key, {
                enumerable: true,
                configurable: true,
                get: function () {
                    latestDeps = deps;
                    return val;
                },
                set: function (newVal) {
                    if (newVal !== val) {
                        onChange(deps);
                        val = newVal;
                    }
                }
            });
        }
    };

    var injectionCache = {};

    var resolve = function (name) {
        // TODO: check scope and/or cache
        var info = $this.components[name];
        var dependencies = info.dependencies;
        var cache = info.service;
        if (cache && name in injectionCache) {
            return injectionCache[name];
        }
        if (!dependencies) {
            var instance = makeReactive(new window[name]());
            if (cache) {
                injectionCache[name] = instance;
            }
            return instance;
        }
        var arguments = [null];
        for (var i in dependencies) {
            var d = dependencies[i];
            var a = null; // d.null
            if (d.default) {
                a = d.default; // TODO: copy object or array
            } else if (d.builtIn) {
                a = d.name === 'string' ? '' : 0;
            } else {
                a = resolve(d.name);
            }
            arguments.push(a);
        }
        var instance = makeReactive(new (window[name].bind.apply(window[name], arguments))());
        if (cache) {
            injectionCache[name] = instance;
        }
        return instance;
    }

    var create = function (name, childNodes, attributes) {
        if (!(name in $this.components)) {
            throw new Error('Component ' + name + ' doesn\'t exist.');
        }
        var page = $this.components[name];
        var instance = resolve(name);
        // pass props
        if (attributes) {
            for (var ai in attributes) {
                var attr = attributes[ai];
                if (attr.content in instance) {
                    var propValue = null;
                    var currentValue = null;
                    for (var i in attr.children) {
                        var propExpression = attr.children[i].propExpression;
                        if (propExpression.call) {
                            var args = [attr.instance, $this];
                            if (attr.scope) {
                                for (var k in attr.scope.stack) {
                                    args.push(attr.scope.data[attr.scope.stack[k]]);
                                }
                            }
                            currentValue = propExpression.func.apply(null, args);
                        } else {
                            currentValue = propExpression.content;
                        }
                        if (i > 0) {
                            propValue += currentValue;
                        } else {
                            propValue = currentValue;
                        }
                    }
                    if (propValue === 'true') {
                        propValue = true;
                    } else if (propValue === 'false') {
                        propValue = false;
                    } else if (typeof propValue === 'string' && !isNaN(propValue)) {
                        propValue = +propValue;
                    }
                    instance[attr.content] = propValue;
                }
            }
        }
        console.log(instance, childNodes, attributes);
        return build(page.nodes, instance, childNodes);
    };

    this.render = function (name) {
        if (!name) {
            throw new Error('Component name is required.');
        }
        if (!(name in this.components)) {
            throw new Error('Component ' + name + ' doesn\'t exist.');
        }
        var nodes = create(name);
        // document.childNodes[1].remove();
        console.log(nodes);
        createDOM(document, nodes, false);
    };

    this.htmlentities = function (html) {
        return typeof html === 'string' ? html.replace(/[\u00A0-\u9999<>\&]/gim, function (i) {
            return '&#' + i.charCodeAt(0) + ';';
        }) : html;
    }
}
var app = new Edgeon();
var notify = app.notify;
app.start();
