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
    this.componentsUrl = '/public/build/components.json';
    this.components = {};
    this.start = function () {
        ajax.get(this.componentsUrl)
            .then(function (components) {
                $this.components = components;
                $this.render('HomePage');
            });
    };
    var currentComponent = null;

    var getDataExpression = function (item) {
        // Function.apply(null, ['a', 'return a;'])
        var itsEvent = arguments.length > 1 && arguments[1];
        if (item.expression) {
            var contentExpression = { call: true };
            var args = ['_component', 'app'];
            if (itsEvent) {
                args.push('event');
            }
            if (item.raw) {
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
        var childs = parent.childs;
        var currentNodeList = [];
        var skip = false;
        var node = false;
        var previousNode = null;
        var usedSubscriptions = {};
        for (var i in childs) {
            var item = childs[i];

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
                    slotName = slotNameItem.childs[0].content;
                    slotNameExpression = function (x) {
                        return x.attributes
                            && x.attributes.first(function (y) {
                                return y.content === 'name'
                                    && y.childs[0].content === slotName;
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
                        items.each(function (x) {
                            x.nextNode = null;
                            x.parent = parentNode;
                            x.previousNode = prevNode;
                            if (prevNode) {
                                prevNode.nextNode = x;
                            }
                            prevNode = x;
                        });
                        currentNodeList = currentNodeList.concat(items);
                        console.log(currentNodeList);
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
                            slotContent.childs.each(function (x) {
                                x.nextNode = null;
                                x.parent = parentNode;
                                x.previousNode = prevNode;
                                if (prevNode) {
                                    prevNode.nextNode = x;
                                }
                                prevNode = x;
                            });
                            currentNodeList = currentNodeList.concat(slotContent.childs);
                        }
                    }
                    previousNode = currentNodeList.length > 0
                        ? currentNodeList[currentNodeList.length - 1]
                        : null;
                }
                continue;
            }

            if (item.type === 'text' && node && node.type === 'text') {
                node.contents.push(getDataExpression(item));
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
                contents: [getDataExpression(item)],
                domNode: null, // DOM node if rendered
                // conditions: [true], // collection of expressions (if,else,elseif)
                parent: parentNode, // TODO: make imutable
                count: 1, // 0 - hidden, 1 - show, > 1 - foreach
                take: 1, // 1 - default, > 1 - <template or <component, take next n items
                skip: false, // if was taken by previous then false
                data: false, // foreach target here
                instance: instance,
                previousNode: previousNode
            };
            if (previousNode) {
                previousNode.nextNode = node;
            }
            previousNode = node;
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
                node.childs = build({ childs: [item] }, instance, stack, node);
                if (conditionalTypes.indexOf(node.type) !== -1) {
                    node.condition = specialType.childs
                        ? getDataExpression(specialType.childs[0])
                        : {};
                    for (var s in usedSubscriptions) {
                        listenTo(node, s);
                    }
                    if (specialType.childs && specialType.childs[0].subs) { // TOD: subscribe all if-else group to each sub changes
                        for (var s in specialType.childs[0].subs) {
                            listenTo(node, specialType.childs[0].subs[s]);
                            usedSubscriptions[specialType.childs[0].subs[s]] = true;
                        }
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
            // childs
            childNodes = false;
            if (item.childs) {
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
                            copy.content = a.content;
                            copy.isAttribute = true;
                            copy.parent = node;
                            copy.contentExpression = getDataExpression(a);
                            if (a.childs) {
                                copy.childs = a.childs.select(
                                    function (v) {
                                        var valCopy = {};
                                        valCopy.contentExpression = getDataExpression(v, itsEvent);
                                        valCopy.content = v.content;
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
                var componenNodes = create(component, childNodes);
                currentNodeList = currentNodeList.concat(componenNodes);
            } else {
                if (childNodes) {
                    node.childs = childNodes;
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
                var actionContent = attr.childs[0].contentExpression.func;
                console.log(elm, eventName, attr.childs); // TODO: attach event data $event
                elm.addEventListener(eventName, function ($event) {
                    actionContent(attr.parent.instance, $this, $event);
                });
            } else {
                // TODO: process if, else-if, else
                var val =
                    attr.childs ?
                        attr.childs.select(
                            function (x) {
                                return x.contentExpression.call
                                    ? x.contentExpression.func(attr.parent.instance, $this)
                                    : x.contentExpression.content;
                            }
                        ).join('')
                        : '';
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
                        if (potencialNode.isVirtual && potencialNode.childs) {
                            potencialNode = potencialNode.childs[potencialNode.childs.length - 1];
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
                // TODO: remove childs
                // TODO: on remove rerender sibilings before and after if text or virtual
                nodes[i].domNode.parentNode.removeChild(nodes[i].domNode);
                nodes[i].domNode = null;
            }
            if (nodes[i].childs) {
                removeDomNodes(nodes[i].childs);
            }
        }
    }

    var createDomNode = function (parent, node, insert, skipGroup) {
        if (!skipGroup) {
            if (node.isVirtual || node.type === 'text') {
                // TODO: make property which says if text or virtual node has text or virtual sibilings
                // TODO: make property which indicates if node is text type (shortness)
                // TODO: make track property to set render version and render group once
                // render fresh DOM from first text/virtual to the last text/virtual
                // TODO: save rebder version and rerender only once node.v = renderVersion (update renderVersion on changes)
                // TODO: set if node needs rerender sibiling on build/comlile stage
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
            // console.log(node.childs[0].contents[0].content, node);
            var condition = node.parent && node.parent.condition;
            var active = condition && condition.value;
            if (condition && !active) { // remove
                removeDomNodes([node]);
                return;
            }
        }
        var texts = [];
        for (var i in node.contents) {
            var contentExpression = node.contents[i];
            texts.push(contentExpression.call ? contentExpression.func(node.instance, $this) : contentExpression.content);
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
                            parent.appendChild(elm);
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
            default:
                throw new Error('Node type \'' + node.type + '\' is not implemented.');
        }
        elm && createDOM(elm, node.childs, nextInsert, skipGroup);

    }

    var createDOM = function (parent, nodes, insert, skipGroup) {
        for (var i in nodes) {
            createDomNode(parent, nodes[i], insert, skipGroup);
        }
    }

    var nextInstanceId = 0;

    var getInstanceId = function (instance) {
        if (!('__id' in instance)) {
            instance.__id = ++nextInstanceId;
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

    var onChange = function (instance, path) {
        // console.log('changed: ', instance, path);
        var iid = getInstanceId(instance);
        if (iid in subscribers
            && path in subscribers[iid]) {
            renderQueue[iid + '_' + path] = subscribers[iid][path];
            setTimeout(function () {
                reRender();
            }, 0);
        }
    };

    var makeReactive = function (obj) {
        var instance = arguments.length > 1 ? arguments[1] : obj;
        var path = arguments.length > 2 ? arguments[2] : 'this';
        if ('__reactive' in obj) {
            return obj;
        }
        obj['__reactive'] = true;
        var keys = Object.keys(obj);
        for (var i = 0; i < keys.length; i++) {
            defineReactive(instance, path + '.' + keys[i], obj, keys[i], obj[keys[i]]);
        }
        return obj;
    };

    var defineReactive = function (instance, path, obj, key, val) {
        if (val !== null && typeof val === 'object') {
            makeReactive(val, instance, path);
        }
        if (typeof val === 'function') { // reactive methods ???
            return;
        }
        Object.defineProperty(obj, key, {
            enumerable: true,
            configurable: true,
            get: function () {
                return val;
            },
            set: function (newVal) {
                onChange(instance, path);
                val = newVal;
            }
        });
    };

    var resolve = function (name) {
        // TODO: check scope and/or cache
        var dependencies = $this.components[name].dependencies;
        if (!dependencies) {
            return makeReactive(new window[name]());
        }
        var arguments = [];
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
        return makeReactive(new (window[name].bind.apply(window[name], arguments))());
    }

    var create = function (name, childNodes) {
        if (!(name in $this.components)) {
            throw new Error('Component ' + name + ' doesn\'t exist.');
        }
        var page = $this.components[name];
        var instance = resolve(name);
        console.log(instance);
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
app.start();
