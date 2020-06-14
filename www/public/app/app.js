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
            if (x(this[k])) {
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
    var nodes = [];
    var build = function (parent) {
        var stack = arguments.length > 1 ? arguments[1] : false;
        var childs = parent.childs;
        var currentNodeList = [];
        for (var i in childs) {
            var item = childs[i];

            var component = false;

            var node = {
                type: item.type,
                childs: [],
                contents: [item.content], // text groups
                conditions: [true], // collection of expressions (if,else,elseif)
                parent: parent,
                count: 1, // 0 - hidden, 1 - show, > 1 - foreach
                take: 1, // 1 - default, > 1 - <template or <component, take next n items
                skip: false, // if was taken by previous then false
                data: false, // foreach target here
                domNodes: [] // DOM node if rendered
            };
            var domNode = {
                content: false, // final content
                active: true, // if,else,elseif
                key: false, // key of foreach iteration
                item: false, // value of foreach iteration
                parent: node
            };
            node.domNodes.push(domNode);
            if (item.type === 'component') {
                component = item.content;
            }
            if (item.type === 'tag' && item.content === 'slot') {
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
                // TODO: reassign parents
                if (stack) {
                    if (slotName === 0) {
                        var items = stack.where(function (x) {
                            return x.contents[0] !== 'slotContent';
                        });
                        console.log(items);
                        currentNodeList = currentNodeList.concat(items);
                    } else {
                        var slotContent = stack.first(function (x) {
                            return x.type === 'tag'
                                && x.contents[0] === 'slotContent'
                                && slotNameExpression(x);
                        });
                        if (slotContent) {
                            currentNodeList = currentNodeList.concat(slotContent.childs);
                        }
                    }
                }
            }
            //     switch (item.type) {
            //         case 'component': {
            //             // push into component's slots
            //             component = item.content;
            //             break;
            //         }
            //         case 'text': {
            //             break;
            //         }
            //         case 'tag': {
            //             break;
            //         }
            //         case 'attr': {
            //             break;
            //         }
            //         case 'value': {
            //             break;
            //         }
            //         default:
            //             throw new Error('Node type \'' + item.type + '\' is not implemented.');
            //     }

            // attributes

            // childs
            childNodes = false;
            if (item.childs) {
                childNodes = build(item, stack);
            }
            if (item.attributes) {
                node.attributes = item.attributes;
            }
            if (component) {
                if (!(component in $this.components)) {
                    throw new Error('Component ' + component + ' doesn\'t exist.');
                }
                var page = $this.components[component];
                var componenNodes = build(page, childNodes);
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
    this.render = function (name) {
        if (!name) {
            throw new Error('Component name is required.');
        }
        if (!(name in this.components)) {
            throw new Error('Component ' + name + ' doesn\'t exist.');
        }
        var page = this.components[name];
        nodes = build(page);
        console.log(nodes);
    };

}
var app = new Edgeon();
app.start();
