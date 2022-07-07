function Viewi() {
    var $this = this;
    var availableTags = {};
    var booleanAttributes = {};
    var resourceTags = {};
    var trimExpr = /^\s*|\s*$/g;
    var trimSemicolonExpr = /;$/g;
    var events = {
        onViewiUrlChange: null
    };
    'img,embed,object,link,script,audio,video,style'
        .split(',')
        .each(function (t) {
            resourceTags[t] = true;
        });

    var resourcesCache = [];

    var router = new Router();
    this.componentsUrl = VIEWI_PATH + '/components.json' + VIEWI_VERSION;
    this.components = {};
    var htmlElementA = document.createElement('a');
    var hydrate = false;
    var config = null;

    var getPathName = function (href) {
        htmlElementA.href = href;
        return htmlElementA.pathname;
    };

    var cacheResources = function () {
        for (var tag in resourceTags) {
            var elements = document.getElementsByTagName(tag);
            for (var i = 0; i < elements.length; i++) {
                resourcesCache.push(elements[i]);
            }
        }
    }

    var startInternal = function () {
        $this.components._meta.tags.split(',').each(function (x) {
            availableTags[x] = true;
        });
        $this.components._meta.boolean.split(',').each(function (x) {
            booleanAttributes[x] = true;
        });
        $this.components._routes.each(function (x) {
            router.register(x.method, x.url, x.component);
        });
        config = $this.components._config;
        cacheResources();
        hydrate = true;
        $this.go(location.href, false);
    };

    this.getConfig = function () {
        return config;
    }

    this.start = function () {
        if (typeof onViewiUrlChange !== 'undefined'
            && typeof onViewiUrlChange === 'function') {
            events.onViewiUrlChange = onViewiUrlChange;
        }
        if (typeof ViewiPages !== 'undefined') {
            $this.components = JSON.parse(ViewiPages);
            startInternal();
        } else {
            ajax.get(this.componentsUrl)
                .then(function (components) {
                    $this.components = components;
                    startInternal();
                });
        }

        // catch all local A tags click
        document.addEventListener('click', function (e) {
            e = e || window.event;
            if (e.defaultPrevented) {
                return;
            }
            var target = e.target || e.srcElement;
            var aTarget = target;
            if (aTarget.parentNode && aTarget.parentNode.tagName === 'A') {
                aTarget = aTarget.parentNode;
            }
            if (aTarget.tagName === 'A' && aTarget.href && aTarget.href.indexOf(location.origin) === 0) {
                getPathName(aTarget.href);
                if (
                    !htmlElementA.hash
                    || htmlElementA.pathname !== location.pathname
                ) {
                    e.preventDefault(); // Cancel the native event
                    // e.stopPropagation(); // Don't bubble/capture the event
                    $this.go(aTarget.href, true);
                    if (htmlElementA.hash) {
                        htmlElementA.click();
                    }
                }
            }
        }, false);

        // handle back button
        window.addEventListener('popstate', function (e) {
            if (e.state)
                $this.go(e.state.href, false);
            else
                $this.go(location.href, false);
        });
    };

    this.go = function (href, isForward) {
        var url = getPathName(href);
        events.onViewiUrlChange && events.onViewiUrlChange(url);
        var routeItem = router.resolve(url);
        if (routeItem == null) {
            throw 'Can\'t resolve route for uri: ' + url;
        }
        if (isForward) {
            window.history.pushState({ href: href }, '', href);
        }
        $this.render(routeItem.item.action, routeItem.params);
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
            if (itsEvent || item.setter) {
                args.push('event');
            }
            if (item.scope) {
                args = args.concat(item.scope.stack);
            } else {
                args = args.concat(currentScope);
            }
            item.code = item.code.replace(trimExpr, '');
            item.code = item.code.replace(trimSemicolonExpr, '');
            if (item.setter) {
                args.push(
                    item.isChecked ?
                        'if(Array.isArray(' + item.code + ')) { '
                        + ' if (' + item.code + '.indexOf(event.target.value) === -1) { '
                        + 'event.target.checked && ' + item.code + '.push(event.target.value);'
                        + 'event.target.checked && notify(' + item.code + '); '
                        + ' } else { '
                        + '!event.target.checked && ' + item.code + '.splice(' + item.code + '.indexOf(event.target.value), 1);'
                        + '!event.target.checked && notify(' + item.code + '); '
                        + ' } '
                        + ' } else { '
                        + item.code + ' = event.target.checked;'
                        + ' } '
                        : (item.code + ' = event.target.multiple ? Array.prototype.slice.call(event.target.options)'
                            + '.where(function(x){ return x.selected; })'
                            + '.select(function(x){ return x.value; })'
                            + ' : event.target.value;')

                );
            }
            else if (item.raw || forceRaw) {
                args.push('return ' + item.code + ';');
            } else {
                args.push('return app.htmlentities(' + item.code + ');');
            }
            contentExpression.code = item.code;
            contentExpression.func = Function.apply(null, args);
            return contentExpression;
        }
        return { call: false, content: item.raw ? item.content : $this.decode(item.content) };
    }

    var specialTags = ['template'];
    var specialTypes = ['if', 'else-if', 'else', 'foreach'];
    var conditionalTypes = ['if', 'else-if', 'else'];
    var requirePreviousIfTypes = ['else-if', 'else'];
    var usedSpecialTypes = [];

    var build = function (parent, instance) {
        var stack = arguments.length > 2 ? arguments[2] : false;
        var owner = arguments.length > 3 ? arguments[3] : null;
        var parentNode = owner && !owner.isRoot ? owner : null;
        var isRoot = arguments.length > 4 ? arguments[4] : false;
        var children = parent.children;
        var currentNodeList = [];
        var skip = false;
        var node = false;
        var previousNode = null;
        var usedSubscriptions = {};
        for (var i in children) {
            var item = children[i];
            if (item.type === 'tag' && item.content === 'slot') {
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
                var useDefault = true;
                if (stack) {
                    if (slotName === 0) {
                        var items = stack.where(function (x) {
                            return x.type !== 'tag' || x.contents[0].content !== 'slotContent';
                        });
                        if (items.length > 0) {
                            useDefault = false;
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
                        }
                    } else {
                        var slotContent = stack.first(function (x) {
                            return x.type === 'tag'
                                && x.contents[0].content === 'slotContent'
                                && slotNameExpression(x);
                        });
                        if (slotContent) {
                            useDefault = false;
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
                }
                if (useDefault) {
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

            if (!item.raw && node && ((item.type === 'text' && node.type === 'text')
                || (item.type === 'comment' && node.type === 'comment'))
            ) {
                node.contents.push(getDataExpression(item, instance));
                if (item.subs) {
                    node.subs = (node.subs || []).concat(item.subs);
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
                id: ++nextNodeId,
                type: item.type,
                contents: [getDataExpression(item, instance)],
                domNode: null, // DOM node if rendered
                parent: parentNode, // TODO: make immutable
                instance: instance,
                previousNode: previousNode,
                subs: item.subs
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
                node.componentChildren = item.children;
                node.isVirtual = true;
            }
            if (specialType === null && item.type === 'tag' && specialTags.indexOf(item.content) !== -1) {
                var specialTag = item.content;
                node.type = specialTag;
                node.isVirtual = true;
            }
            if (parentNode && parentNode.scope) {
                node.scope = parentNode.scope;
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
                    if (specialType.children && specialType.children[0].subs) { // TODO: subscribe all if-else group to each sub changes
                        node.subs = specialType.children[0].subs;
                        for (var s in specialType.children[0].subs) {
                            usedSubscriptions[specialType.children[0].subs[s]] = true;
                        }
                    }
                }
                var codeChild = false;
                if (node.type === 'foreach') {
                    // compile foreach expression
                    if (specialType.children) {
                        codeChild = specialType.children[0];
                        if (codeChild.subs) {
                            node.subs = (node.subs || []).concat(codeChild.subs);
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
                            stack: [], //currentScope.slice(),
                            data: {}
                        }
                        // console.log(node, node.forExpression, currentScope);
                    }
                }
                node.children = build({ children: [item] }, instance, stack, node);
                if (node.childInstances) {
                    node.children[0].childInstances = node.childInstances;
                    delete node.childInstances;
                }
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
                            var itsEvent = a.expression ? false : a.content[0] === '(';
                            copy.content = a.content; // keep it for slots
                            copy.isAttribute = true;
                            copy.parent = node;
                            copy.contentExpression = getDataExpression(a, instance);
                            copy.instance = node.instance;
                            if (a.dynamic) {
                                copy.dynamic = a.dynamic;
                            }
                            if (node.scope) {
                                copy.scope = node.scope;
                            }
                            if (a.children) {
                                copy.children = a.children.select(
                                    function (v) {
                                        var valCopy = {};
                                        var forceRaw = a.content === 'model';
                                        valCopy.contentExpression = getDataExpression(v, instance, itsEvent, forceRaw);
                                        if (node.type === 'dynamic'
                                            || node.type === 'component'
                                        ) { // we need props
                                            valCopy.propExpression = getDataExpression(v, instance, null, true);
                                            if (v.subs) {
                                                valCopy.subs = v.subs;
                                            }
                                        }

                                        valCopy.content = v.content; // keep it for slots
                                        if (v.subs && !itsEvent) {
                                            valCopy.subs = (valCopy.subs || []).concat(v.subs);
                                        }
                                        return valCopy;
                                    }
                                );
                            }
                            if (a.subs && !itsEvent) {
                                copy.subs = (copy.subs || []).concat(a.subs);
                            }
                            copy.origin = a;
                            return copy;
                        }
                    );
            }
            if (component) {
                // compare component and reuse if matched
                // if reused refresh slots
                var resetReuse = false;
                if (isRoot) {
                    reuseEnabled = true;
                    resetReuse = true;
                }
                var componentNodes = create(component, childNodes, node.attributes);
                if (!owner.childInstances) {
                    owner.childInstances = [];
                }
                owner.childInstances.push(componentNodes);
                if (resetReuse) {
                    reuseEnabled = false;
                }

                var currentVersion = 'main';
                if (componentNodes.wrapper.hasVersions) {
                    createInstance(componentNodes.wrapper);
                    mountInstance(componentNodes.wrapper);
                    currentVersion = componentNodes.wrapper
                        && componentNodes.wrapper.component.__version
                        && componentNodes.wrapper.component.__version();
                    if (!currentVersion) {
                        throw new Error("Component '" + componentNodes.wrapper.name + "' doesn't support versioning!");
                    }
                }
                if (!(currentVersion in componentNodes.versions)) {
                    throw new Error("Can't find version '" + currentVersion + "' for component '" + componentNodes.wrapper.name + "'!");
                }

                var prevNode = currentNodeList.length > 0
                    ? currentNodeList[currentNodeList.length - 1]
                    : null;
                var toConcat = [];
                componentNodes.versions[currentVersion].each(function (x) {
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
                //     && componentNodes.length > 0) {
                //     componentNodes[0].previousNode = currentNodeList[currentNodeList.length - 1];
                //     currentNodeList[currentNodeList.length - 1].nextNode = componentNodes[0];
                // }
                // currentNodeList = currentNodeList.concat(componentNodes);
            } else {
                if (childNodes) {
                    if (node.type === 'dynamic' || node.type === 'raw') {
                        node.itemChildren = childNodes;
                    } else {
                        node.children = childNodes;
                    }
                }
                currentNodeList.push(node);
            }
        }
        return currentNodeList;
    };

    var mountInstance = function (wrapper) {
        if (wrapper.isMounted) return;
        wrapper.component.__beforeMount && wrapper.component.__beforeMount();
        if (wrapper.attributes) {
            for (var ai in wrapper.attributes) {
                var attr = wrapper.attributes[ai];
                // console.log('attribute', attr, wrapper.component);
                // TODO: DRY (attribute event)
                var itsEvent = attr.content[0] === '(';
                if (itsEvent) { // event emitter
                    var attrName = attr.content;
                    var eventName = attrName.substring(1, attrName.length - 1);
                    var actionContent = null;
                    if (attr.dynamic) {
                        if (!attr.eventExpression) {
                            if (!attr.instance.component) {
                                attr.instance.component = createInstance(attr.instance);
                            }
                            attr.eventExpression = getDataExpression(attr.dynamic, attr.instance, true);
                            // console.log(attr.eventExpression);
                        }
                        actionContent = attr.eventExpression.func;
                    } else {
                        actionContent = attr.children[0].contentExpression.func;
                    }
                    if (!attr.listeners) {
                        attr.listeners = {};
                    }
                    attr.listeners[attrName] && elm.removeEventListener(eventName, attr.listeners[attrName]);
                    attr.listeners[attrName] = function ($event) {
                        //actionContent(attr.instance.component, $this, $event);
                        var args = [attr.instance.component, $this, $event];
                        if (attr.scope) {
                            for (var k in attr.scope.stack) {
                                args.push(attr.scope.data[attr.scope.stack[k]]);
                            }
                        }
                        actionContent.apply(null, args);
                    };
                    if (!wrapper.component.$_callbacks) {
                        wrapper.component.$_callbacks = {};
                    }
                    wrapper.component.$_callbacks[eventName] = attr.listeners[attrName];
                }
                var propValue = null;
                var currentValue = null;
                for (var i in attr.children) {
                    var propExpression = attr.children[i].propExpression;
                    if (propExpression.call) {
                        if (!attr.instance.component) {
                            attr.instance.component = createInstance(attr.instance);
                        }
                        var args = [attr.instance.component, $this];
                        if (attr.scope) {
                            for (var k in attr.scope.stack) {
                                args.push(attr.scope.data[attr.scope.stack[k]]);
                            }
                        }
                        currentValue = itsEvent ? propExpression.func : propExpression.func.apply(null, args);
                        if (attr.children[i].subs) {
                            propsSubs['this.' + attr.content] = {
                                instance: attr.instance,
                                subs: attr.children[i].subs
                            };
                        }
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
                if (attr.content in wrapper.component) {
                    wrapper.component[attr.content] = propValue;
                    // console.log(['mount', wrapper.name, attr.content, propValue, wrapper]);
                    // if(propValue === undefined) debugger;
                }
                wrapper.component._props[attr.content] = propValue;
            }
        }
        // console.log(['mount', wrapper.name, wrapper]);
        wrapper.component.__mounted && wrapper.component.__mounted();
        wrapper.isMounted = true;
    }

    var createInstance = function (wrapper) {
        if (wrapper.component) {
            if (wrapper.attributes) {
                for (var i = 0; i < wrapper.attributes.length; i++) {
                    wrapper.attributes[i].origin.childComponent = wrapper.component;
                }
            }
            return;
        }
        var component = resolve(wrapper.name, wrapper.params, wrapper.__id);
        wrapper.component = component;
        wrapper.isCreated = true;
        if (wrapper.attributes) {
            for (var i = 0; i < wrapper.attributes.length; i++) {
                var attribute = wrapper.attributes[i];
                var itsEvent = attribute.expression ? false : attribute.content[0] === '(';
                attribute.origin.childComponent = component;
                if (!itsEvent) {
                    if (attribute.subs) {
                        for (var s in attribute.subs) {
                            listenTo(attribute, attribute.subs[s]);
                        }
                    }
                    if (attribute.children) {
                        for (var acI = 0; acI < attribute.children.length; acI++) {
                            var attributeChild = attribute.children[acI];
                            if (attributeChild.subs) {
                                for (var s in attributeChild.subs) {
                                    listenTo(attribute, attributeChild.subs[s]);
                                }
                            }
                        }
                    }
                }
            }
        }
        // console.log(['create', wrapper.name, wrapper]);
        // if(wrapper.name === 'Row') debugger;
        onRenderedTracker[wrapper.name] = wrapper;
        return component;
    }

    var renderAttribute = function (elm, attr, eventsOnly) {
        if (!elm) {
            return;
        }
        try {
            var attrName = attr.content;
            if (attr.contentExpression.call) {
                if (!attr.instance.component) {
                    attr.instance.component = createInstance(attr.instance);
                }
                var args = [attr.instance.component, $this];
                if (attr.scope) {
                    for (var k in attr.scope.stack) {
                        args.push(attr.scope.data[attr.scope.stack[k]]);
                    }
                }
                attrName = attr.contentExpression.func.apply(null, args);
                if (attr.latestValue && attr.latestValue !== attrName) {
                    elm.removeAttribute(attr.latestValue);
                    if (attr.listeners && attr.latestValue in attr.listeners) {
                        var eventName = attr.latestValue.substring(1, attr.latestValue.length - 1);
                        elm.removeEventListener(eventName, attr.listeners[attr.latestValue]);
                    }

                }
                attr.latestValue = attrName;
            }
            if (attrName[0] === '(') {
                if (hydrate && !eventsOnly) {
                    return; // no events just yet
                }
                var eventName = attrName.substring(1, attrName.length - 1);
                var actionContent = null;
                if (attr.dynamic) {
                    if (!attr.eventExpression) {
                        if (!attr.instance.component) {
                            attr.instance.component = createInstance(attr.instance);
                        }
                        attr.eventExpression = getDataExpression(attr.dynamic, attr.instance, true);
                        // console.log(attr.eventExpression);
                    }
                    actionContent = attr.eventExpression.func;
                } else {
                    actionContent = attr.children[0].contentExpression.func;
                }
                if (!attr.listeners) {
                    attr.listeners = {};
                }
                attr.listeners[attrName] && elm.removeEventListener(eventName, attr.listeners[attrName]);
                attr.listeners[attrName] = function ($event) {
                    //actionContent(attr.instance.component, $this, $event);
                    var args = [attr.instance.component, $this, $event];
                    if (attr.scope) {
                        for (var k in attr.scope.stack) {
                            args.push(attr.scope.data[attr.scope.stack[k]]);
                        }
                    }
                    actionContent.apply(null, args);
                };
                elm.addEventListener(eventName, attr.listeners[attrName]);
            } else {
                if (eventsOnly && attrName !== 'model') {
                    return;
                }
                if (attr.subs && !attr.subscribed) {
                    for (var s in attr.subs) {
                        listenTo(attr, attr.subs[s]);
                    }
                    attr.subscribed = true;
                }
                var boolean = false;
                var exprValue = true;
                if (attrName in booleanAttributes) {
                    boolean = true;
                }
                var texts = [];
                for (var i in attr.children) {
                    if (attr.children[i].subs && !attr.children[i].subscribed) {
                        for (var s in attr.children[i].subs) {
                            listenTo(attr, attr.children[i].subs[s]);
                        }
                        attr.children[i].subscribed = true;
                    }
                    var contentExpression = attr.children[i].contentExpression;
                    if (contentExpression.call) {
                        if (!attr.instance.component) {
                            attr.instance.component = createInstance(attr.instance);
                        }
                        var args = [attr.instance.component, $this];
                        if (attr.scope) {
                            for (var k in attr.scope.stack) {
                                args.push(attr.scope.data[attr.scope.stack[k]]);
                            }
                        }
                        exprValue = contentExpression.func.apply(null, args);
                        texts.push(exprValue);
                    } else {
                        texts.push(contentExpression.content);
                    }
                }
                if (boolean) {
                    if (exprValue) {
                        elm.setAttribute(attrName, attrName);
                    } else {
                        elm.removeAttribute(attrName);
                    }
                    return;
                }
                var isModel = attrName === 'model';
                var val = texts.join('');
                if (!isModel) {
                    if (texts[0] === null) {
                        elm.removeAttribute(attrName);
                    } else {
                        if (elm.getAttribute(attrName) !== val) {
                            elm.setAttribute(attrName, val);
                        }
                    }
                } else // isModel
                {
                    if (hydrate && !eventsOnly) {
                        return; // no events just yet
                    }
                    var isChecked = elm.getAttribute('type') === 'checkbox';
                    var isRadio = elm.getAttribute('type') === 'radio';
                    var isSelect = elm.tagName === 'SELECT';
                    var isMultiple = isSelect && elm.multiple;
                    var isBoolean = isChecked
                        || isRadio;
                    if (!isBoolean && !isMultiple) {
                        elm.value = val;
                    }
                    if (isRadio) {
                        var hasChecked = elm.getAttribute('checked');
                        if (elm.value != val && hasChecked) {
                            elm.removeAttribute('checked');
                            elm.checked = false;
                        } else if (elm.value == val && !hasChecked) {
                            elm.setAttribute('checked', 'checked');
                            elm.checked = true;
                        }
                    }
                    var eventName = isBoolean || isSelect ? 'change' : 'input';
                    if (!attr.listeners) {
                        attr.listeners = {};
                    }
                    if (!attr.valueExpression && attr.children.length > 0) {
                        if (!attr.instance.component) {
                            attr.instance.component = createInstance(attr.instance);
                        }
                        var args = [attr.instance.component, $this];
                        var scopeArgs = [];
                        if (attr.scope) {
                            for (var k in attr.scope.stack) {
                                scopeArgs.push(attr.scope.data[attr.scope.stack[k]]);
                            }
                        }
                        attr.valueExpression = getDataExpression({
                            code: attr.children[0].contentExpression.code,
                            expression: true,
                            setter: true,
                            isChecked: isChecked,
                            isMultiple: isMultiple,
                            scope: attr.scope
                        }, attr.instance);
                        var actionContent = attr.valueExpression.func;
                        attr.listeners[eventName] = function ($event) {
                            actionContent.apply(null, args.concat([$event]).concat(scopeArgs));
                        };

                    }
                    elm.removeEventListener(eventName, attr.listeners[eventName]);
                    elm.addEventListener(eventName, attr.listeners[eventName]);
                }
            }
        } catch (ex) {
            console.error(ex);
            console.log(attr.content);
        }
    }

    var getFirstBefore = function (node) {
        var nodeBefore = node;
        var skipCurrent = true;
        var itsParent = false;
        var infLoop = false;
        while (!nodeBefore.domNode || skipCurrent) {
            skipCurrent = false;
            if (nodeBefore.previousNode !== null) {
                nodeBefore = nodeBefore.previousNode;
                itsParent = false;
                if (nodeBefore.isVirtual) {
                    if (nodeBefore === infLoop) {
                        throw new Error("Can't find the node before current!");
                    }
                    infLoop = nodeBefore;
                    // go down
                    var potentialNode = nodeBefore;
                    while (potentialNode !== null && !potentialNode.domNode) {
                        if (potentialNode.isVirtual && potentialNode.children) {
                            potentialNode = potentialNode.children[potentialNode.children.length - 1];
                        } else {
                            potentialNode = null;
                        }
                    }
                    if (potentialNode) {
                        nodeBefore = potentialNode;
                    }
                }
            } else {
                if (nodeBefore.parent === null) {
                    return null;
                }
                // go up
                nodeBefore = nodeBefore.parent;
                itsParent = true;
            }
        }
        return { itsParent: itsParent, node: nodeBefore };
    }

    var nextNodeId = 0;
    var renderScopeStack = [];

    var createDomNode = function (parent, node, insert, skipGroup) {
        if (!skipGroup) {
            if (node.isVirtual || node.type === 'text') {
                // TODO: make property which says if text or virtual node has text or virtual siblings
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
        if (node.renderIteration === renderIteration) {
            // node has been rendered already, skipping
            return;
        }
        node.renderIteration = renderIteration;
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
        if (!node.instance.component) {
            node.instance.component = createInstance(node.instance);
        }
        if (!node.instance.isMounted) {
            mountInstance(node.instance);
        }
        var texts = [];
        for (var i in node.contents) {
            var contentExpression = node.contents[i];
            if (contentExpression.call) {
                var args = [contentExpression.instance.component, $this];
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
        if (node.skipIteration) {
            node.skipIteration = false;
            var vvn = node;
            while (vvn.isVirtual) {
                vvn = vvn.parent;
            }
            elm = vvn.domNode;
            var saved = currentLevelDomArray.first(
                /**
                 * 
                 * @param {Node} x 
                 * @param {number} index 
                 */
                function (x, index) {
                    return x === elm;
                },
                true
            );
            if (saved && saved[0].parentNode) {
                takenDomArray[saved[1]] = true;
            }
            if (node.refreshAttributes && node.attributes) {
                node.refreshAttributes = false;
                for (var a in node.attributes) {
                    renderAttribute(elm, node.attributes[a]);
                }
            }
        } else {
            switch (node.type) {
                case 'text': { // TODO: implement text insert
                    if (parent.doctype) {
                        elm = parent.doctype; // TODO: check for <!DOCTYPE html> node
                        break;
                    }
                    var skip = false;
                    var nodeBefore = getFirstBefore(node);
                    if (insert) {
                        // look up for previous text to append or insert after first tag
                        if (nodeBefore == null) {
                            return; // throw error ??
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
                                if (node.domNode.nodeValue !== val) {
                                    node.domNode.nodeValue = val;
                                }
                                elm = node.domNode;
                            } else {
                                elm = document.createTextNode(val);
                                var nextSibling = nodeBefore.node.domNode.nextSibling;
                                if (!nodeBefore.itsParent && nextSibling !== null) {
                                    nextSibling.parentNode.insertBefore(elm, nextSibling);
                                } else if (nodeBefore.itsParent) {
                                    if (nodeBefore.node.domNode.childNodes.length > 0) {
                                        nodeBefore.node.domNode.insertBefore(elm, nodeBefore.node.domNode.childNodes[0]);
                                    } else {
                                        nodeBefore.node.domNode.appendChild(elm);
                                    }
                                } else {
                                    (nodeBefore.node.domNode.parentNode || parent).appendChild(elm);
                                }
                                node.domNode = elm;
                            }
                        }
                    }
                    break;
                }
                case 'tag': {
                    if (parent.documentElement) {
                        elm = parent.documentElement;
                        node.domNode = elm;
                        takenDomArray[0] = true;
                        takenDomArray[1] = true;
                        break;
                    }
                    if (!hydrate && val in resourceTags) {
                        // create to compare
                        var newNode = elm = document.createElement(val);
                        node.domNode = elm;
                        createDOM(newNode, node, node.children, nextInsert, skipGroup);
                        if (node.attributes) {
                            for (var a in node.attributes) {
                                renderAttribute(newNode, node.attributes[a]);
                            }
                        }
                        // search in parent
                        var equalNode = currentLevelDomArray.first(
                            function (x, index) {
                                return !(index in takenDomArray) && x.isEqualNode(newNode);
                            },
                            true
                        );
                        if (equalNode) {
                            takenDomArray[equalNode[1]] = true;
                            node.domNode = equalNode[0];
                            // console.log('Reusing from DOM', equalNode[0]);
                            // put in correct order
                            var nodeBefore = getFirstBefore(node);
                            var nextSibling = nodeBefore.node.domNode.nextSibling;
                            if (!nodeBefore.itsParent && nextSibling !== null) {
                                nextSibling.parentNode.insertBefore(equalNode[0], nextSibling);
                            } else if (nodeBefore.itsParent) {
                                nodeBefore.node.domNode.appendChild(equalNode[0]);
                            } else {
                                nodeBefore.node.domNode.parentNode.appendChild(equalNode[0]);
                            }
                            break;
                        }
                        // search in resourcesCache                    
                        if (!equalNode) {
                            // TODO: filter: if not used (parentNode == null && !HTML)
                            equalNode = resourcesCache.first(
                                function (x) {
                                    return x.isEqualNode(newNode);
                                }
                            );
                            if (equalNode) {
                                node.domNode = equalNode;
                                // console.log('Reusing from cache', equalNode);
                                // put in correct order
                                var nodeBefore = getFirstBefore(node);
                                var nextSibling = nodeBefore.node.domNode.nextSibling;
                                if (!nodeBefore.itsParent && nextSibling !== null) {
                                    nextSibling.parentNode.insertBefore(equalNode, nextSibling);
                                } else if (nodeBefore.itsParent) {
                                    nodeBefore.node.domNode.appendChild(equalNode);
                                } else {
                                    nodeBefore.node.domNode.parentNode.appendChild(equalNode);
                                }
                                break;
                            }
                        }
                        // isEqualNode
                        // skip script for now, TODO: process scripts, styles
                    }
                    if (!hydrate && val === 'head') {
                        var firstMatch = currentLevelDomArray.first(
                            function (x) {
                                return x.nodeName.toLowerCase() === val;
                            },
                            true
                        );
                        elm = firstMatch[0];
                        takenDomArray[firstMatch[1]] = true;
                        node.domNode = elm;
                        // console.log('taking head from DOM');
                        break;
                    }

                    var existentElm = cleanRender ? currentLevelDomArray.first(
                        /**
                         * 
                         * @param {Node} x 
                         * @param {number} index 
                         */
                        function (x, index) {
                            return x.nodeName.toLowerCase() === val && !(index in takenDomArray);
                        },
                        true
                    ) : null;

                    if (existentElm && existentElm[0].parentNode) {
                        takenDomArray[existentElm[1]] = true;
                        if (currentElemPosition == existentElm[1]) {
                            // reuse
                            // TODO: clear attributes
                            elm = existentElm[0];
                            node.domNode = elm;
                            break;
                        }
                        else if (!(existentElm[1] in takenDomArray)) {
                            existentElm[0].parentNode.removeChild(existentElm[0]);
                        }
                    }
                    elm = document.createElement(val);

                    if (node.domNode !== null && node.domNode.parentNode) {
                        node.domNode.parentNode.replaceChild(elm, node.domNode);
                        node.children && removeDomNodes(node.children);
                    } else {
                        var nodeBefore = getFirstBefore(node);
                        if (nodeBefore == null) {
                            return;
                            break; // throw error ??
                        }
                        var nextSibling = nodeBefore.node.domNode.nextSibling;
                        if (!nodeBefore.itsParent && nextSibling !== null) {
                            nextSibling.parentNode.insertBefore(elm, nextSibling);
                        } else if (nodeBefore.itsParent) {
                            // nodeBefore.node.domNode.appendChild(elm);
                            if (nodeBefore.node.domNode.childNodes.length > 0) {
                                nodeBefore.node.domNode.insertBefore(elm, nodeBefore.node.domNode.childNodes[0]);
                            } else {
                                nodeBefore.node.domNode.appendChild(elm);
                            }
                        } else {
                            (nodeBefore.node.domNode.parentNode || parent).appendChild(elm);
                        }
                    }
                    node.domNode = elm;
                    if (val in resourceTags) {
                        resourcesCache.push(elm);
                    }
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
                        // if (insert) {
                        // find first previous not virtual up tree non virtual
                        var nodeBefore = getFirstBefore(node);
                        if (nodeBefore == null) {
                            return;
                            break; // throw error ??
                        }
                        var nextSibling = nodeBefore.node.domNode.nextSibling;
                        if (!nodeBefore.itsParent && nextSibling !== null) {
                            nextSibling.parentNode.insertBefore(elm, nextSibling);
                        } else if (nodeBefore.itsParent) {
                            if (nodeBefore.node.domNode.childNodes.length > 0) {
                                nodeBefore.node.domNode.insertBefore(elm, nodeBefore.node.domNode.childNodes[0]);
                            } else {
                                nodeBefore.node.domNode.appendChild(elm);
                            }
                        } else {
                            nodeBefore.node.domNode.parentNode.appendChild(elm);
                        }
                        // } else {
                        //     parent.appendChild(elm);
                        // }
                    }
                    node.domNode = elm;
                    break;
                }
                case 'if': {
                    // TODO: check condition
                    // TODO: if false remove node if exists
                    // TODO: if true create element
                    usedSubscriptions = {};
                    if (!node.instance.component) {
                        node.instance.component = createInstance(node.instance);
                    }
                    var args = [node.instance.component, $this];
                    if (node.scope) {
                        for (var k in node.scope.stack) {
                            args.push(node.scope.data[node.scope.stack[k]]);
                        }
                    }
                    var nextValue = !!node.condition.func.apply(null, args);
                    if (node.condition.value !== undefined && nextValue === node.condition.value) {
                        return; // nothing's changed
                    }
                    node.condition.value = nextValue;
                    elm = parent;
                    node.parentDomNode = parent;
                    node.condition.previousValue = node.condition.value;
                    node.topRealPreviousNode = (node.parent && node.parent.topRealPreviousNode) || node.previousNode;
                    nextInsert = true;
                    break;
                }
                case 'else-if': {
                    if (!node.instance.component) {
                        node.instance.component = createInstance(node.instance);
                    }
                    var args = [node.instance.component, $this];
                    if (node.scope) {
                        for (var k in node.scope.stack) {
                            args.push(node.scope.data[node.scope.stack[k]]);
                        }
                    }
                    var nextValue = !node.previousNode.condition.value
                        && !!node.condition.func.apply(null, args);

                    node.condition.previousValue = node.previousNode.condition.value || nextValue;
                    if (node.condition.value !== undefined && nextValue === node.condition.value) {
                        return; // nothing's changed
                    }
                    node.condition.value = nextValue;
                    elm = parent;
                    node.parentDomNode = parent;
                    node.topRealPreviousNode = (node.parent && node.parent.topRealPreviousNode) || node.previousNode;
                    nextInsert = true;
                    break;
                }
                case 'else': {
                    var nextValue = !node.previousNode.condition.previousValue;
                    if (node.condition.value !== undefined && nextValue === node.condition.value) {
                        return; // nothing's changed
                    }
                    node.condition.value = nextValue;
                    elm = parent;
                    node.parentDomNode = parent;
                    node.topRealPreviousNode = (node.parent && node.parent.topRealPreviousNode) || node.previousNode;
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
                    // console.log('foreach');
                    if (elm) {
                        // create n nodes (copy of children) and render
                        if (!node.itemChildren) { // TODO: bug, need to rewrite
                            node.itemChildren = node.children;
                        }
                        // removeDomNodes(node.children);
                        // node.children = null;
                        if (!node.instance.component) {
                            node.instance.component = createInstance(node.instance);
                        }
                        var args = [node.instance.component, $this];
                        if (node.scope) {
                            for (var k in node.scope.stack) {
                                args.push(node.scope.data[node.scope.stack[k]]);
                            }
                        }
                        var data = node.forExpression.data.apply(null, args);
                        // if (data && data.length > 0) {
                        //     node.children = [];
                        // }
                        var prevNode = null;
                        var isNumeric = Array.isArray(data);
                        var used = {};
                        var keepIndexes = {};
                        var newChildren = [];
                        for (var k in data) {
                            var dataKey = isNumeric ? +k : k;
                            var found = false;
                            if (node.children) {
                                for (var i = 0; i < node.children.length; i++) {
                                    if (k in used || i in keepIndexes) {
                                        continue;
                                    }
                                    if (node.children[i].foreachData === data[k]) {
                                        used[k] = true;
                                        node.children[i].previousNode = prevNode;
                                        if (prevNode) {
                                            prevNode.nextNode = node.children[i];
                                        }
                                        prevNode = node.children[i];
                                        node.children[i].scope.data[node.forExpression.key] = dataKey;
                                        newChildren.push(node.children[i]);
                                        keepIndexes[i] = true;
                                        found = true;
                                        break;
                                    }
                                }
                            }
                            if (!found) {
                                var wrapperNode = {
                                    type: 'template',
                                    isVirtual: true,
                                    parent: node,
                                    previousNode: prevNode,
                                    instance: Object.assign({}, node.instance),
                                    domNode: null,
                                    scope: node.scope,
                                    foreachData: data[k],
                                    foreachKey: dataKey
                                };
                                wrapperNode.scope = {
                                    parentScope: node.scope,
                                    data: Object.assign({}, node.scope.data),
                                    stack: node.scope.stack.slice()
                                }
                                if (prevNode) {
                                    prevNode.nextNode = wrapperNode;
                                }
                                prevNode = wrapperNode;
                                wrapperNode.scope.stack.push(node.forExpression.key);
                                wrapperNode.scope.stack.push(node.forExpression.value);
                                wrapperNode.scope.data[node.forExpression.key] = dataKey;
                                wrapperNode.scope.data[node.forExpression.value] = data[k];
                                scopeNodes(wrapperNode, node.itemChildren);
                                newChildren.push(wrapperNode);
                            }
                        }
                        // remove dom nodes in unused
                        if (node.children != null) {
                            if (newChildren.length > 0) {
                                for (var i = node.children.length - 1; i >= 0; i--) {
                                    if (!(i in keepIndexes)) {
                                        removeDomNodes([node.children[i]]);
                                    }
                                }
                            } else {
                                removeDomNodes(node.children);
                            }
                        }
                        // set new children
                        if (newChildren.length > 0) {
                            node.children = newChildren;
                        } else {
                            node.children = null;
                        }
                        // TODO: resubscribe for changes, remove subscriptions for itemChildren

                    }
                    // console.log(node, data);
                    break;
                }
                case 'dynamic': { // dynamic tag or component
                    // wrap into template
                    // clone child for tag
                    // or build component
                    // render
                    elm = parent;
                    nextInsert = true;
                    if (node.latestVal !== val) {
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
                        if (node.itemChildren) {
                            copyNodes(wrapperNode, node.itemChildren);
                        }
                        if (val in availableTags) { // it's a tag
                            wrapperNode.type = 'tag';
                            wrapperNode.root = node.root;
                        } else {
                            // build component
                            // componentChildren
                            var dynamicNodes = create(val, wrapperNode.children, node.attributes);
                            createInstance(dynamicNodes.wrapper);
                            mountInstance(dynamicNodes.wrapper);
                            instantiateChildren(dynamicNodes.root);
                            wrapperNode.type = 'template';
                            wrapperNode.isVirtual = true;
                            wrapperNode.children = dynamicNodes.versions['main'];
                            // reassign parent
                            wrapperNode.children.each(function (x) {
                                x.parent = wrapperNode;
                            });
                        }
                        node.children = [wrapperNode];
                    }
                    node.latestVal = val;
                    // console.log(node, val);
                    break;
                }
                case 'raw': {
                    elm = parent;
                    nextInsert = true;
                    if (node.latestHtml === val) {
                        if (node.rawNodes) {
                            for (var i = 0; i < node.rawNodes.length; i++) {
                                node.rawNodes[i].domNode.usedByRenderer = true;
                            }
                        }
                        return;
                    }
                    node.latestHtml = val;
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
                        // console.log({ d: vdom });
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
                            var nextSibling = firstClosest.node.domNode.nextSibling;
                            if (newNodes.length > 0) {
                                for (var i = 0; i < newNodes.length; i++) {
                                    if (!firstClosest.itsParent && nextSibling) {
                                        nextSibling.parentNode.insertBefore(newNodes[i].domNode, nextSibling);
                                    } else if (firstClosest.itsParent) {
                                        firstClosest.node.domNode.appendChild(newNodes[i].domNode);
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
                    if (node.rawNodes) {
                        for (var i = 0; i < node.rawNodes.length; i++) {
                            node.rawNodes[i].domNode.usedByRenderer = true;
                        }
                    }
                    if (node.subs && !node.subscribed) {
                        for (var s in node.subs) {
                            listenTo(node, node.subs[s]);
                        }
                        node.subscribed = true;
                    }
                    return; // do not run children
                }
                default:
                    throw new Error('Node type \'' + node.type + '\' is not implemented.');
            }
        }
        if (!node.isVirtual && elm && node.root) {
            node.instance.component._element = elm;
        }
        if (elm) {
            elm.usedByRenderer = true;
        }
        // TODO: cover cases: if, else if, else
        if (node.subs && !node.subscribed) {
            for (var s in node.subs) {
                listenTo(node, node.subs[s]);
            }
            node.subscribed = true;
        }
        elm && createDOM(elm, node, node.children, nextInsert, skipGroup);
    }

    var currentParent = null;
    var currentLevelDomArray = [];
    var takenDomArray = {};
    var cleanRender = false;
    var currentElemPosition = 0;

    var createDOM = function (parent, node, nodes, insert, skipGroup) {
        var previousParent = currentParent;
        var previousLevelDomArray = currentLevelDomArray;
        var previousTakenDomArray = takenDomArray;
        currentParent = parent;

        if (parent.childNodes && !node.isVirtual) {
            for (var i = 0; i < parent.childNodes.length; i++) {
                if ('usedByRenderer' in parent.childNodes[i]) {
                    parent.childNodes[i].usedByRenderer = false;
                }
            }
        }

        if (cleanRender && parent !== previousParent) {
            currentLevelDomArray = Array.prototype.slice.call(currentParent.childNodes);
            takenDomArray = {};
        }
        for (var i in nodes) {
            // if (nodes[i].skipIteration) {
            var saved = currentLevelDomArray.first(
                function (x, index) {
                    return x === nodes[i].domNode;
                },
                true
            );
            if (saved && saved[0].parentNode) {
                takenDomArray[saved[1]] = true;
            }
            // }
        }
        for (var i in nodes) {
            currentElemPosition = i;
            nodes[i].childInstances && instantiateChildren(nodes[i]);
            createDomNode(parent, nodes[i], insert, skipGroup);
        }
        if (cleanRender) {
            // currentLevelDomArray.each(function (x, k) {
            //     if (
            //         !(k in takenDomArray)
            //         && x.parentNode
            //     ) {
            //         x.parentNode.removeChild(x);
            //     }
            // });
        }
        if (parent.childNodes && !node.isVirtual) {
            for (var i = parent.childNodes.length - 1; i >= 0; i--) {
                if (!parent.childNodes[i].usedByRenderer) {
                    if ('usedByRenderer' in parent.childNodes[i]) {
                        parent.removeChild(parent.childNodes[i]);
                    }
                }
            }
        }
        currentParent = previousParent;
        currentLevelDomArray = previousLevelDomArray;
        takenDomArray = previousTakenDomArray;
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

    var removeDomNodes = function (nodes, silent) {
        for (var k in nodes) {
            if (nodes[k].children) {
                removeDomNodes(nodes[k].children, silent);
            }
            if (nodes[k].domNode) {
                if (nodes[k].domNode.parentNode) {
                    nodes[k].domNode.parentNode.removeChild(nodes[k].domNode);
                } else if (!silent) {
                    // console.log('Can\'t remove', nodes[k]);
                }
                nodes[k].domNode = null;
            }
            if (conditionalTypes.includes(nodes[k].type)) {
                delete nodes[k].condition.value;
            }
            if (nodes[k].skipIteration) {
                nodes[k].skipIteration = false;
            }
            if (nodes[k].origin) {
                for (var oi in nodes[k].origin) {
                    if (nodes[k].origin[oi]) {
                        nodes[k].origin[oi].skipIteration = false;
                        nodes[k].origin[oi].domNode = null;
                        nodes[k].origin[oi] = null;
                    }
                }
            }
        }
    }

    var instancesScope = {};
    var cleanInstance = false;
    var scopeNodes = function (parent, nodes) {
        instancesScope = {};
        cleanInstance = true;
        copyNodes(parent, nodes);
        cleanInstance = false;
    };

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
        if (!parent.itemChildren) {
            parent.children = newChildren;
        } else {
            parent.itemChildren = newChildren;
        }
    };

    var cloneNode = function (parent, node) {
        var copy = Object.assign({}, node);
        node.cloned = true;
        delete copy.cloned;
        delete copy.subscribed;
        copy.nextNode = null;
        copy.previousNode = null;
        copy.domNode = null;
        copy.scope = parent.scope || copy.scope;
        if (node.condition) {
            if (conditionalTypes.includes(node.type)) {
                copy.condition = Object.assign({}, node.condition);
            } else {
                copy.condition = parent.condition;
            }
        }
        if (cleanInstance) {
            if (!copy.instance.component) {
                if (copy.instance.__id in instancesScope) {
                    copy.instance = instancesScope[copy.instance.__id];
                } else {
                    copy.instance = Object.assign({}, node.instance);
                    instancesScope[copy.instance.__id] = copy.instance;
                    copy.instance.__id = ++nextInstanceId;
                    instancesScope[copy.instance.__id] = copy.instance;
                }
                copy.instance.attributes = copy.instance.attributes.select(function (x) {
                    var attr = Object.assign({}, x);
                    attr.scope = copy.scope || attr.scope;
                    attr.origin = Object.assign({}, x.origin);
                    delete attr.origin.childComponent;
                    return attr;
                });
            }
            // TODO: new __id for each iteration level, keep instances
            if (node.childInstances) {
                copy.childInstances = node.childInstances.select(function (x) {
                    if (!x.wrapper.component) {
                        var childInstance = Object.assign({}, x);
                        if (x.wrapper.__id in instancesScope) {
                            childInstance.wrapper = instancesScope[x.wrapper.__id];
                        } else {
                            childInstance.wrapper = Object.assign({}, x.wrapper);
                            instancesScope[childInstance.wrapper.__id] = childInstance.wrapper;
                            childInstance.wrapper.__id = ++nextInstanceId;
                            instancesScope[childInstance.wrapper.__id] = childInstance.wrapper;
                            childInstance.wrapper.attributes = x.wrapper.attributes.select(function (y) {
                                var attr = Object.assign({}, y);
                                attr.scope = copy.scope || attr.scope;
                                return attr;
                            });
                        }
                        return childInstance;
                    }
                    return x;
                });
            }
            copy.contents = copy.contents.select(function (x) {
                var content = Object.assign({}, x);
                if (content.call && content.instance.__id in instancesScope) {
                    content.instance = instancesScope[content.instance.__id];
                }
                return content;
            });
        }
        if (copy.attributes) {
            copy.attributes = copy.attributes.select(function (a) {
                var aCopy = Object.assign({}, a);
                aCopy.parent = copy;
                a.cloned = true;
                delete aCopy.cloned;
                delete aCopy.subscribed;
                if (cleanInstance && !aCopy.instance.component) {
                    if (aCopy.instance.__id in instancesScope) {
                        aCopy.instance = instancesScope[aCopy.instance.__id]
                    } else {
                        aCopy.instance = Object.assign({}, node.instance);
                        instancesScope[aCopy.instance.__id] = aCopy.instance;
                        aCopy.instance.__id = ++nextInstanceId;
                        instancesScope[aCopy.instance.__id] = aCopy.instance;
                        // console.log(instancesScope);
                        aCopy.instance.attributes = aCopy.instance.attributes.select(function (x) {
                            var attr = Object.assign({}, x);
                            attr.scope = aCopy.scope || attr.scope;
                            return attr;
                        });
                    }
                }

                aCopy.scope = copy.scope || aCopy.scope;
                if (a.children) {
                    a.children = a.children.select(function (attributeChildren) {
                        var attributeChildrenCopy = Object.assign({}, attributeChildren);
                        attributeChildrenCopy.parent = copy;
                        attributeChildren.cloned = true;
                        delete attributeChildrenCopy.cloned;
                        delete attributeChildrenCopy.subscribed;
                        return attributeChildrenCopy;
                    });
                }
                return aCopy;
            });
        }
        // console.log(copy.instance);
        // copy.instance = Object.assign({}, node.instance);
        // copy.instance.component = null;
        if (node.children || node.itemChildren) {
            copyNodes(copy, node.children || node.itemChildren);
        }
        return copy;
    };

    var nextInstanceId = 0;

    var getInstanceId = function (instance, id) {
        if (!('__id' in instance)) {
            Object.defineProperty(instance, "__id", {
                enumerable: false,
                writable: false,
                value: id || ++nextInstanceId
            });
        }
        return instance.__id;
    }

    var renderQueue = {};
    var subscribers = {};
    var propsSubs = {};

    var listenTo = function (node, path) {
        var isAttribute = node.isAttribute;
        var instance = isAttribute ? node.parent.instance : node.instance;
        var iid = getInstanceId(instance);
        if (!(iid in subscribers)) {
            subscribers[iid] = {};
        }
        var pathParts = path.split('.');
        var firstPart = pathParts[0];
        var paths = [];
        for (var i = 1; i < pathParts.length; i++) {
            firstPart = firstPart + '.' + pathParts[i];
            paths.push(firstPart);
        }
        for (var i = 0; i < paths.length; i++) {
            var pathToListen = paths[i];
            if (!(pathToListen in subscribers[iid])) {
                subscribers[iid][pathToListen] = [];
            }
            subscribers[iid][pathToListen].push(node);

            if (pathToListen in propsSubs) {
                var propSubscription = propsSubs[pathToListen];
                var propInstance = propSubscription.instance;
                var propPaths = propSubscription.subs;
                var iidProp = getInstanceId(propInstance);
                if (!(iidProp in subscribers)) {
                    subscribers[iidProp] = {};
                }
                for (var p in propPaths) {
                    var propPath = propPaths[p];
                    if (!(propPath in subscribers[iidProp])) {
                        subscribers[iidProp][propPath] = [];
                    }
                    subscribers[iidProp][propPath].push(node);
                }
            }
        }
    }

    // TODO: on change conditions, insert element if new, remove if not active
    // TODO: try catch
    var reRender = function () {
        var queue = renderQueue;
        renderQueue = {};
        renderIteration++;
        for (var path in queue) {
            for (var i in queue[path]) {
                try {
                    var node = queue[path][i];
                    if (node.isAttribute) {
                        var domNode = node.parent.type === 'dynamic' ? (node.parent.children && node.parent.children.length > 0 && node.parent.children[0].domNode) : node.parent.domNode;
                        domNode && renderAttribute(domNode, node);
                        if (node.parent.type === 'component' && node.origin.childComponent) {
                            // reassign property
                            var args = [node.instance.component, $this];
                            if (node.scope) {
                                for (var k in node.scope.stack) {
                                    args.push(node.scope.data[node.scope.stack[k]]);
                                }
                            }
                            currentValue = null;
                            for (var childIndex = 0; childIndex < node.children.length; childIndex++) {
                                var contentValue = node.children[childIndex].propExpression.call
                                    ? node.children[childIndex].propExpression.func.apply(null, args)
                                    : node.children[childIndex].propExpression.content;
                                currentValue = childIndex === 0 ? contentValue : currentValue + contentValue;
                            }
                            node.origin.childComponent[node.content] = currentValue;
                        }
                    } else if (node.isVirtual) {
                        createDomNode(node.parentDomNode, node);
                    } else {
                        var parentDomNode = node.domNode && node.domNode.parentNode;
                        if (!parentDomNode) {
                            var parentOrBeforeNode = getFirstBefore(node);
                            parentDomNode = parentOrBeforeNode && parentOrBeforeNode.node.domNode && parentOrBeforeNode.node.domNode.parentNode;
                        }
                        parentDomNode && createDomNode(parentDomNode, node);
                    }
                } catch (error) {
                    console.error(error);
                }
            }
        }
        Object.keys(renderQueue).length > 0 && reRender();
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
        if ('deps' in p) {
            for (var k in deps) {
                if (!(k in a.deps)) {
                    a.deps[k] = {}
                }
                for (var t in deps[k]) {
                    a.deps[k][t] = deps[k][t];
                }
            }
            return;
        }
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
                defineReactive(instance, path + '[key]', obj, keys[i]);
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
                        if (val !== null && typeof val === 'object') {
                            makeReactive(val, instance, path, deps);
                        }
                    }
                }
            });
        }
    };

    var injectionCache = {};

    var resolve = function (name, params, id) {
        // TODO: check scope and/or cache
        var info = $this.components[name];
        if (!info) {
            return null;
        }
        var dependencies = info.dependencies;
        var cache = info.service;
        if (cache && name in injectionCache) {
            return injectionCache[name];
        }
        if (!dependencies) {
            var instance = new window[name]();
            if (info.init) {
                instance.__init();
            }
            getInstanceId(instance, id);
            makeReactive(instance);
            if (cache) {
                injectionCache[name] = instance;
            }
            return instance;
        }
        var arguments = [null];
        for (var i in dependencies) {
            var d = dependencies[i];
            var a = null; // d.null
            if (params && (d.argName in params)) {
                a = params[d.argName];
            }
            else if (d.default) {
                a = d.default; // TODO: copy object or array
            } else if (d.null) {
                a = null;
            } else if (d.builtIn) {
                a = d.name === 'string' ? '' : 0;
            } else {
                a = resolve(d.name);
            }
            arguments.push(a);
        }
        var instance = info.init
            ? new window[name]()
            : new (window[name].bind.apply(window[name], arguments))();
        if (info.init) {
            arguments.shift();
            instance.__init.apply(instance, arguments);
        }
        getInstanceId(instance, id);
        makeReactive(instance);
        if (cache) {
            injectionCache[name] = instance;
        }
        return instance;
    }

    var mergeNodes = function (a, b) {
        var randI = 0;
        var la = a.length;
        var lb = b.length;
        if (la != lb) {
            // console.log('Length is different', la, lb, a, b);
            // temp solution, remove all b
            removeDomNodes(b);
            for (var i = 0; i < la; i++) {
                if (a[i].skipIteration) {
                    a[i].skipIteration = false;
                    a[i].domNode = null;
                    a[i].children && removeDomNodes(a[i].children, true);
                }
            }
            return; // TODO: match each node individually
        }
        for (var i = 0; i < la; i++) {
            if (i < lb) {
                var matched = true;
                var hasCode = false;
                var ac = a[i].contents && a[i].contents.select(function (x) { return x.content || (x.code); }).join(); // && ++randI
                var bc = b[i].contents && b[i].contents.select(function (x) { return x.content || (x.code); }).join();
                if (a[i].instance.name != b[i].instance.name) {
                    // console.log('Instances don\'t match', [a[i].instance, b[i].instance], a[i], b[i]);
                    matched = false;
                }
                if (ac != bc) {
                    // console.log('Contents don\'t match', [ac, bc], a[i], b[i]);
                    matched = false;
                }
                // compare attributes
                // attributes[0].content attributes[0].instance attributes[0].content.children[0].content
                var aa = a[i].attributes && a[i].attributes.select(function (x) {
                    return (x.content || '') +
                        ((x.children && ';' + x.children.select(function (y) {
                            return y.contentExpression.content || (y.contentExpression.code) || ''
                        }).join(';')) || '');
                }).join(';');
                var ba = b[i].attributes && b[i].attributes.select(function (x) {
                    return (x.content || '') +
                        ((x.children && ';' + x.children.select(function (y) {
                            return y.contentExpression.content || (y.contentExpression.code) || ''
                        }).join(';')) || '');
                }).join(';');
                if (aa != ba) { // TODO: compare attr instance and attr values
                    // console.log('Attributes don\'t match', aa, ba);
                    matched = false;
                }
                if (matched || (a[i].type === 'dynamic' && b[i].type === 'dynamic')) {
                    // all matched, reassigning DOM node
                    hasCode = (a[i].contents && a[i].contents.first(function (x) { return x.code; }))
                    // ||
                    a[i].domNode = b[i].domNode;
                    a[i].skipIteration = !a[i].isVirtual && !!b[i].domNode && !hasCode;
                    if (a[i].skipIteration) {
                        if (!b[i].origin) {
                            b[i].origin = {};
                        }
                        b[i].origin[a[i].id] = a[i];
                        a[i].refreshAttributes = (a[i].attributes && a[i].attributes.first(function (x) {
                            return x.children && x.children.first(function (y) {
                                return y.contentExpression.code;
                            });
                        }));
                        if (a[i].refreshAttributes) {
                            // reattach instances
                            a[i].attributes.each(function (attr, index) {
                                attr.latestValue = b[i].attributes[index].latestValue;
                                attr.listeners = b[i].attributes[index].listeners;
                            });
                        }
                    }
                    if (b[i].rawNodes) {
                        a[i].rawNodes = b[i].rawNodes;
                        a[i].latestHtml = b[i].latestHtml;
                    }
                    // console.log('Merged:', a[i], b[i]);
                    if (a[i].children && b[i].children) {
                        mergeNodes(a[i].children, b[i].children);
                    }
                } else {
                    // remove DOM node
                    if (b[i].domNode && b[i].domNode.parentNode) {
                        b[i].domNode.parentNode.removeChild(b[i].domNode);
                    } else if (b[i].isVirtual) {
                        if (a[i].previousNode && a[i].previousNode.type === 'text' && a[i].previousNode.skipIteration) {
                            a[i].previousNode.skipIteration = false;
                            removeDomNodes([a[i].previousNode]);
                        }
                        removeDomNodes(b[i].children);
                    }
                    a[i].children && removeDomNodes(a[i].children); // TODO: reuse dom items
                    if (b[i].rawNodes) {
                        a[i].rawNodes = b[i].rawNodes;
                        a[i].latestHtml = b[i].latestHtml;
                    }
                }
            }
        }
    };

    var parentComponentName = null;

    var create = function (name, childNodes, attributes, params, isRoot) {
        if (!(name in $this.components)) {
            throw new Error('Component ' + name + ' doesn\'t exist.');
        }
        var builtNodes = null;
        var instanceWrapper = null;
        // if (parentComponentName === currentPage.name) {
        // reuse wrapper components
        if (reuseEnabled) {
            var same = latestPage.components.first(function (x) {
                return x.name === name;
            }, true);
            if (same) {
                latestPage.components.splice(same[1], 1);
                builtNodes = same[0].build;
                instanceWrapper = same[0].instanceWrapper; // TODO: default values restore
                instanceWrapper.isMounted = false;
                instanceWrapper.attributes = attributes;
                instanceWrapper.params = params;
                // console.log('Reusing', instance);
            }
        }
        // }
        var previousName = parentComponentName;
        parentComponentName = name;
        var page = $this.components[name];
        instanceWrapper = instanceWrapper || {
            component: null,
            name: name,
            isMounted: false,
            isCreated: false,
            params: params,
            hasVersions: page.hasVersions,
            attributes: attributes,
            __id: ++nextInstanceId
        }
        var root = { isRoot: true };
        var newBuild = {
            root: root,
            versions: {},
            wrapper: instanceWrapper
        };
        if (page.hasVersions) {
            for (var ver in page.versions) {
                newBuild.versions[ver] = build(page.versions[ver], instanceWrapper, childNodes, root, isRoot);
                if (builtNodes) {
                    mergeNodes(newBuild.versions[ver], builtNodes.versions[ver]);
                }
            }
        } else {
            newBuild.versions['main'] = build(page.nodes, instanceWrapper, childNodes, root, isRoot);
            if (builtNodes) {
                mergeNodes(newBuild.versions['main'], builtNodes.versions['main']);
            }
        }
        for (var ver in newBuild.versions) {
            for (var i = 0; i < newBuild.versions[ver].length; i++) {
                newBuild.versions[ver][i].root = true;
            }
        }
        // console.log(instance, newBuild, builtNodes, childNodes, attributes);
        parentComponentName = previousName;
        // if (parentComponentName === currentPage.name) {
        currentPage.components.push({
            name: name,
            build: newBuild,
            instanceWrapper: instanceWrapper
        });
        // }
        return newBuild;
    };

    var currentPage = {
        name: null,
        nodes: null,
        components: []
    };

    var latestPage = {
        name: null,
        nodes: null,
        components: []
    };

    var collectVirtual = function (node, vNodes) {
        if (node.children) {
            for (var c in node.children) {
                if (node.children[c].domNode) {
                    vNodes.push(node.children[c]);
                } else if (node.children[c].isVirtual) {
                    collectVirtual(node.children[c], vNodes);
                }
            }
        }
    }
    var trimWhitespaceRegex = /^\s+|\s+$/;
    /**
     * 
     * @param {vNode} node 
     * @param {Node} domElement 
     */
    var hydrateDOM = function (node, domElement) {
        // nodeName - tag name
        // nodeType - 1 tag, 3 text, 8 comment
        // nodeValue for text and comment
        var same = node.domNode.nodeType === domElement.nodeType;
        if (same) {
            if (node.domNode.nodeType === 3 || node.domNode.nodeType == 8) {
                same = node.domNode.nodeValue.replace(trimWhitespaceRegex, '') == domElement.nodeValue.replace(trimWhitespaceRegex, '');
                if (same) {
                    node.domNode = domElement;
                }
            } else if (node.domNode.nodeType === 1) {
                // compare 1. tag name, 2.attributes, 3. children, 4. attach events
                // 1. tag name
                same = node.domNode.nodeName == domElement.nodeName;
                if (same) {
                    node.domNode = domElement;
                    if (!node.isVirtual && domElement && node.root && node.instance && node.instance.component) {
                        node.instance.component._element = domElement;
                    }
                    var s = 0; // shift for virtual nodes
                    var shiftDOM = 0; // shift for DOM children
                    var count = domElement.childNodes.length;
                    var maxNodes = node.children ? node.children.length : 0;
                    //if (count < maxNodes) {
                    if (node.children && node.children.length > 0) {
                        // normalize
                        if (
                            node.children[maxNodes - 1].domNode
                            && node.children[maxNodes - 1].domNode.nodeType === 3
                            && (domElement.childNodes.length == 0 || domElement.childNodes[count - 1].nodeType !== 3)
                            && /^\s*$/.test(node.children[maxNodes - 1].domNode.nodeValue)
                        ) {
                            // oldParent.removeChild(node.children[maxNodes - 1].domNode);
                            // node.children.splice(maxNodes - 1, 1);
                            // node.children[maxNodes - 2].nextNode = null;
                            domElement.appendChild(node.children[maxNodes - 1].domNode);
                        }

                        if (
                            node.children[0].domNode
                            && node.children[0].domNode.nodeType === 3
                            && domElement.childNodes[0].nodeType !== 3
                            && /^\s*$/.test(node.children[0].domNode.nodeValue)
                        ) {
                            // oldParent.removeChild(node.children[0].domNode);
                            // node.children.splice(0, 1);
                            // node.children[0].previousNode = null;
                            domElement.insertBefore(node.children[0].domNode, domElement.childNodes[0]);
                        }
                        maxNodes = node.children.length;
                        count = domElement.childNodes.length;
                    }
                    var nodesToRemove = [];
                    var vNodes = [];
                    for (var i = 0; i < maxNodes; i++) {
                        if (node.children[i].rawNodes) {
                            vNodes = vNodes.concat(node.children[i].rawNodes);
                        } else if (node.children[i].isVirtual) {
                            collectVirtual(node.children[i], vNodes);
                        } else {
                            vNodes.push(node.children[i]);
                        }
                    }
                    var vCount = vNodes.length;
                    for (var i = 0; i + shiftDOM < count; i++) {
                        while (i + s < vCount && !vNodes[i + s].domNode) { // TODO: support virtual nodes, dig deeper
                            s++;
                        }
                        if (i + s < vCount) {
                            if (vNodes[i + s].type) {
                                var sameChild = hydrateDOM(vNodes[i + s], domElement.childNodes[i + shiftDOM]);
                                if (!sameChild) {
                                    // try to find next match
                                    var foundNext = false;
                                    for (var ni = i + shiftDOM + 1; ni < count; ni++) {
                                        foundNext = hydrateDOM(vNodes[i + s], domElement.childNodes[ni]);
                                        if (foundNext) {
                                            // remove all not matched before
                                            for (var ri = i + shiftDOM; ri < ni; ri++) {
                                                nodesToRemove.push(ri);
                                            }
                                            shiftDOM += ni - (i + shiftDOM);
                                            break;
                                        }
                                    }
                                    if (foundNext) {
                                        continue;
                                    }
                                    // reattach parent
                                    if (node.domNode !== vNodes[i + s].domNode.parentNode) {
                                        if (node.domNode.childNodes.length > i + shiftDOM) {
                                            node.domNode.replaceChild(vNodes[i + s].domNode, node.domNode.childNodes[i + shiftDOM]);
                                        } else {
                                            node.domNode.appendChild(vNodes[i + s].domNode);
                                        }
                                        hydrateDOM(vNodes[i + s], domElement.childNodes[i + shiftDOM]);
                                    } else {
                                        // two copies, remove one
                                        nodesToRemove.push(i);
                                    }
                                }
                            } else {
                                vNodes[i + s].domNode = domElement.childNodes[i + shiftDOM]; // TODO: compare attributes
                            }
                        } else {
                            // no more nodes to compare, remove dom element 
                            nodesToRemove.push(i + shiftDOM);
                        }
                    }
                    // append the rest of it
                    for (var i = count; i + s < vCount; i++) {
                        // can be null
                        if (vNodes[i + s].domNode) { // TODO: merge text nodes
                            node.domNode.appendChild(vNodes[i + s].domNode);
                        }
                    }
                    if (nodesToRemove.length > 0) {
                        // var oldTotal = node.children ? node.children.length : 0;
                        for (var k = nodesToRemove.length - 1; k >= 0; k--) {
                            var nodeIndex = nodesToRemove[k];
                            // if (nodeIndex < oldTotal) {
                            //     // replace
                            //     domElement.replaceChild(node.children[nodeIndex].domNode, domElement.childNodes[nodeIndex]);
                            // } else {
                            if (['BODY', 'HEAD'].includes(domElement.childNodes[nodeIndex].nodeName)) {
                                // unexpected output from the server, replace rendered
                                domElement.replaceChild(node.children.first(
                                    function (x) { return x.domNode.nodeName && x.domNode.nodeName === domElement.childNodes[nodeIndex].nodeName; }
                                ).domNode, domElement.childNodes[nodeIndex]);
                            } else {
                                domElement.removeChild(domElement.childNodes[nodeIndex]);
                            }
                            // }
                        }
                    }
                    count = domElement.childNodes.length;
                    if (count > vCount) {
                        for (var k = count - 1; k >= vCount; k--) {
                            domElement.removeChild(domElement.childNodes[k]);
                        }
                    }
                    if (vCount > count) {
                        for (var k = count; k < vCount; k++) {
                            if (vNodes[k].domNode && !vNodes[k].type) { // raw html from external js
                                domElement.appendChild(vNodes[k].domNode);
                            }
                        }
                    }
                    // 4. attach events
                    if (node.attributes) {
                        for (var a in node.attributes) {
                            renderAttribute(node.domNode, node.attributes[a], true);
                        }
                    }
                }
            }
            if (node.domNode) {
                node.domNode.usedByRenderer = true;
            }
        }
        return same;
    }

    var reuseEnabled = false;
    var scroll = false;

    var instantiateChildren = function (root) {
        if (root.childInstances) {
            for (var i in root.childInstances) {
                createInstance(root.childInstances[i].wrapper);
                mountInstance(root.childInstances[i].wrapper);
                root.childInstances[i].root.childInstances
                    && instantiateChildren(root.childInstances[i].root);
            }
        }
    }

    var onRenderedTracker = {};
    var renderInProgress = false;
    var setAbort = false;
    var abortRender = false;
    var renderIteration = 0;

    this.render = function (name, params, force) {
        if (!name) {
            throw new Error('Component name is required.');
        }
        if (!(name in $this.components)) {
            throw new Error('Component ' + name + ' doesn\'t exist.');
        }
        if ($this.components[name].lazyLoad) {
            var lazyLoadGroup = $this.components[name].lazyLoad;
            // lazy load the component
            var lazyGroupUrl = VIEWI_PATH + '/' + lazyLoadGroup + '.group.json' + VIEWI_VERSION;
            var resolvedSuccessfully = false;
            ajax.get(lazyGroupUrl)
                .then(function (group) {
                    for (var componentName in group) {
                        delete $this.components[componentName]['lazyLoad'];
                        $this.components[componentName] = Object.assign($this.components[componentName], group[componentName]);
                        if (componentName === name) {
                            resolvedSuccessfully = true;
                        }
                    }
                    if (resolvedSuccessfully) {
                        // resume render
                        $this.render(name, params, force);
                    } else {
                        console.error('Component "' + name + '" was not found in the group: ' + lazyGroupUrl);
                    }
                }, function () {
                    console.error('Failed to lazy load the component in the group: ' + lazyGroupUrl);
                });
            return;
        }
        onRenderedTracker = {};
        if (!force && window[name] && window[name]._beforeStart) {
            var middlewareList = window[name]._beforeStart;
            if (middlewareList.length > 0) {
                var mI = middlewareList.length - 1;
                var middlewareName = middlewareList[mI];
                /**
                 * @type {{run: (next: Function) => {}}}
                 */
                var middleware = resolve(middlewareName);
                var next = function () {
                    var runNext = arguments.length > 0 ? arguments[0] : true; // true by default
                    if (runNext) {
                        if (mI > 0) {
                            mI--;
                            middlewareName = middlewareList[mI];
                            middleware = resolve(middlewareName);
                            middleware.run(next);
                        } else {
                            $this.render(name, params, true);
                        }
                    }
                };
                middleware.run(next);
                return;
            }
        }
        if (renderInProgress) {
            setAbort = true;
        }
        var destroy = function () {
            for (var i = 0; i < latestPage.components.length; i++) {
                var componentBuild = latestPage.components[i];
                if (componentBuild.instanceWrapper
                    && componentBuild.instanceWrapper.component
                    && componentBuild.instanceWrapper.component.__destroy
                ) {
                    componentBuild.instanceWrapper.component.__destroy();
                }
            }
        };
        renderInProgress = true;
        reuseEnabled = false;
        subscribers = {};
        renderQueue = {};
        parentComponentName = null;
        if (setAbort) {
            destroy();
        }
        latestPage = currentPage;
        currentPage = {};
        currentPage.name = name;
        currentPage.components = [];

        var instanceMeta = create(name, null, null, params, true);
        var nodes = instanceMeta.versions['main'];
        currentPage.nodes = nodes;
        scroll && window.scrollTo(0, 0);
        scroll = true;
        cleanRender = !hydrate;
        var target = hydrate ? { documentElement: document.createElement('html'), doctype: {} } : document;
        createInstance(instanceMeta.wrapper);
        // console.log('renderInProgress, setAbort, abortRender', renderInProgress, setAbort, abortRender);
        // TODO: rewrite with cycle/recursion consideration
        if (abortRender) { destroy(); abortRender = false; return; }
        mountInstance(instanceMeta.wrapper);
        if (abortRender) { destroy(); abortRender = false; return; }
        instantiateChildren(instanceMeta.root);
        if (abortRender) { destroy(); abortRender = false; return; }
        destroy();
        renderIteration++;
        createDOM(target, {}, nodes, false);
        // hydrate && console.log(target);
        var nodeToHydrate = nodes[1];
        if (!nodeToHydrate && nodes[0].type === 'dynamic') {
            //              dynamic  template    html
            nodeToHydrate = nodes[0].children[0].children[1];
        }
        hydrate && hydrateDOM(nodeToHydrate, document.documentElement);
        for (var wrapperName in onRenderedTracker) {
            onRenderedTracker[wrapperName].component.__rendered
                && onRenderedTracker[wrapperName].component.__rendered();
        }
        renderInProgress = false;
        if (setAbort) { // TODO: improve abort scenarios
            setAbort = false;
            abortRender = true;
        }
        onRenderedTracker = {};
        cleanRender = false;
        hydrate = false;
    };

    this.htmlentities = function (html) {
        return html;
    }

    var encoder = document.createElement('textarea');

    this.decode = function (text) {
        encoder.innerHTML = text;
        return encoder.value;
    }
}