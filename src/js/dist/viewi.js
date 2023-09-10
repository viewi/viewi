(() => {
  // app/components/CounterReducer.js
  var CounterReducer = class {
    count = 0;
    increment() {
      this.count++;
    }
    decrement() {
      this.count--;
    }
  };

  // app/components/TodoReducer.js
  var TodoReducer = class {
    items = [];
    addNewItem(text) {
      this.items.push(text);
    }
  };

  // viewi/core/BaseComponent.ts
  var BaseComponent = class {
    _props = {};
    $_callbacks = {};
    _refs = {};
    _slots = {};
    _element = null;
    $$t = [];
    // template inline expressions
    $$r = {};
    // reactivity callbacks
    $;
    _name = "BaseComponent";
    emitEvent(name, event) {
      if (this.$_callbacks && name in this.$_callbacks) {
        this.$_callbacks[name](event);
      }
    }
  };

  // app/components/MenuBar.js
  var MenuBar = class extends BaseComponent {
    _name = "MenuBar";
  };

  // app/functions/strlen.js
  function strlen(string) {
    var str = string + "";
    return str.length;
  }

  // app/components/Counter.js
  var Counter = class extends BaseComponent {
    _name = "Counter";
    count = 0;
    message = "My message";
    increment() {
      this.count++;
      this.message += "!";
    }
    decrement() {
      this.count--;
    }
  };
  var Counter_x = [
    function(_component) {
      return function(event) {
        _component.decrement();
      };
    },
    function(_component) {
      return _component.count % 10 + 12;
    },
    function(_component) {
      return "\n    Count " + _component.count + " " + strlen(_component.message) + "\n";
    },
    function(_component) {
      return "\nCount " + _component.count + " " + strlen(_component.message) + "\n";
    },
    function(_component) {
      return function(event) {
        _component.increment();
      };
    },
    function(_component) {
      return function(event) {
        _component.increment(event);
      };
    },
    function(_component) {
      return _component.increment.bind(_component);
    },
    function(_component) {
      return _component.message;
    }
  ];

  // app/components/HomePage.js
  var HomePage = class extends BaseComponent {
    _name = "HomePage";
    title = "Viewi v2 - Build reactive front-end with PHP";
  };
  var HomePage_x = [
    function(_component) {
      return _component.title;
    },
    function(_component) {
      return _component.title;
    }
  ];

  // app/components/Layout.js
  var Layout = class extends BaseComponent {
    _name = "Layout";
    title = "Viewi";
  };
  var Layout_x = [
    function(_component) {
      return "\n        " + _component.title + " | Viewi\n    ";
    }
  ];

  // app/components/NotFoundPage.js
  var NotFoundPage = class extends BaseComponent {
    _name = "NotFoundPage";
  };

  // app/components/CounterPage.js
  var CounterPage = class extends BaseComponent {
    _name = "CounterPage";
  };

  // app/components/TodoAppPage.js
  var TodoAppPage = class extends BaseComponent {
    _name = "TodoAppPage";
  };

  // app/components/StatefulCounter.js
  var StatefulCounter = class extends BaseComponent {
    _name = "StatefulCounter";
    counter = null;
    $message = "Secret message";
    count = null;
    constructor(count2) {
      super();
      this.count = count2 === void 0 ? 0 : count2;
      this.counter = new CounterReducer();
    }
    $calculate() {
      this.count++;
    }
  };
  var StatefulCounter_x = [
    function(_component) {
      return function(event) {
        _component.counter.decrement();
      };
    },
    function(_component) {
      return _component.__id;
    },
    function(_component) {
      return _component.counter.count;
    },
    function(_component) {
      return function(event) {
        _component.counter.increment();
      };
    }
  ];

  // app/functions/count.js
  function count(mixedVar, mode) {
    let key;
    let cnt = 0;
    if (mixedVar === null || typeof mixedVar === "undefined") {
      return 0;
    } else if (mixedVar.constructor !== Array && mixedVar.constructor !== Object) {
      return 1;
    }
    if (mode === "COUNT_RECURSIVE") {
      mode = 1;
    }
    if (mode !== 1) {
      mode = 0;
    }
    for (key in mixedVar) {
      if (mixedVar.hasOwnProperty(key)) {
        cnt++;
        if (mode === 1 && mixedVar[key] && (mixedVar[key].constructor === Array || mixedVar[key].constructor === Object)) {
          cnt += count(mixedVar[key], 1);
        }
      }
    }
    return cnt;
  }

  // app/components/StatefulTodoApp.js
  var StatefulTodoApp = class extends BaseComponent {
    _name = "StatefulTodoApp";
    text = "";
    todo = null;
    constructor(todo) {
      super();
      this.todo = todo;
    }
    handleSubmit(event) {
      event.preventDefault();
      if (strlen(this.text) == 0) {
        return;
      }
      this.todo.addNewItem(this.text);
      this.text = "";
    }
  };
  var StatefulTodoApp_x = [
    function(_component) {
      return function(event) {
        _component.handleSubmit(event);
      };
    },
    function(_component) {
      return _component.text;
    },
    function(_component) {
      return "\n        Add #" + (count(_component.todo.items) + 1) + "\n    ";
    },
    function(_component) {
      return _component.todo.items;
    }
  ];

  // app/components/TestComponent.js
  var TestComponent = class extends BaseComponent {
    _name = "TestComponent";
    name = "MyName";
    _name2_Test = "MyName_2";
    empty = "";
    null = null;
    url = "/home";
    attr = "title";
    event = "(click)";
    arr = ["a", "b", "c"];
    arrWithKeys = { "a": "Apple", "b": "Orange", "c": "Lemon" };
    arrNested = { "a": { "a": "Apple", "b": "Orange", "c": "Lemon" }, "b": { "a": "Apple", "b": "Orange", "c": "Lemon" }, "c": { "a": "Apple", "b": "Orange", "c": "Lemon" } };
    ifValue = true;
    ifElseValue = true;
    getName(name) {
      var sum = (1 + 5) * 10;
      return name ?? "DefaultName";
    }
    addTodo() {
      this.arr = [...this.arr, "Viewi"];
    }
    onEvent(event) {
      event.preventDefault();
    }
    toggleIf() {
      this.ifValue = !this.ifValue;
    }
    toggleElseIf() {
      this.ifElseValue = !this.ifElseValue;
    }
  };
  var TestComponent_x = [
    function(_component) {
      return "Tag test " + _component.name + " " + _component._name2_Test;
    },
    function(_component) {
      return "\n    $notAVar " + _component.getName() + " " + _component.getName(_component.name) + "\n    Nested\n    ";
    },
    function(_component) {
      return _component.url;
    },
    function(_component) {
      return _component.empty;
    },
    function(_component) {
      return _component.null;
    },
    function(_component) {
      return _component.attr;
    },
    function(_component) {
      return expression.bind(_component);
    },
    function(_component) {
      return _component.event;
    },
    function(_component) {
      return _component.onEvent.bind(_component);
    },
    function(_component) {
      return _component.addTodo.bind(_component);
    },
    function(_component) {
      return _component.arr;
    },
    function(_component, _key1, item) {
      return item;
    },
    function(_component, _key1, item) {
      return item;
    },
    function(_component, _key1, item) {
      return item;
    },
    function(_component) {
      return _component.arr;
    },
    function(_component, index, item) {
      return index;
    },
    function(_component, index, item) {
      return item;
    },
    function(_component, index, item) {
      return index + ". ";
    },
    function(_component, index, item) {
      return item;
    },
    function(_component, index, item) {
      return item;
    },
    function(_component) {
      return _component.arrWithKeys;
    },
    function(_component, index, item) {
      return index;
    },
    function(_component, index, item) {
      return item;
    },
    function(_component, index, item) {
      return index + ": ";
    },
    function(_component, index, item) {
      return index;
    },
    function(_component, index, item) {
      return item;
    },
    function(_component, index, item) {
      return item;
    },
    function(_component) {
      return _component.arrNested;
    },
    function(_component, key, subArr) {
      return subArr;
    },
    function(_component, key, subArr, subKey, subItem) {
      return key;
    },
    function(_component, key, subArr, subKey, subItem) {
      return subKey;
    },
    function(_component, key, subArr, subKey, subItem) {
      return subItem;
    },
    function(_component, key, subArr, subKey, subItem) {
      return key + ". " + subKey + ". " + subItem;
    },
    function(_component) {
      return _component.arrNested;
    },
    function(_component, key, subArr) {
      return key === "b";
    },
    function(_component, key, subArr) {
      return subArr;
    },
    function(_component, key, subArr, subKey, subItem) {
      return key;
    },
    function(_component, key, subArr, subKey, subItem) {
      return subKey;
    },
    function(_component, key, subArr, subKey, subItem) {
      return subItem;
    },
    function(_component, key, subArr, subKey, subItem) {
      return key + ". " + subKey + ". " + subItem;
    },
    function(_component) {
      return _component.toggleIf.bind(_component);
    },
    function(_component) {
      return _component.toggleElseIf.bind(_component);
    },
    function(_component) {
      return _component.ifValue;
    },
    function(_component) {
      return _component.ifElseValue;
    },
    function(_component) {
      return _component.ifValue;
    },
    function(_component) {
      return _component.ifElseValue;
    },
    function(_component) {
      return _component.arr;
    },
    function(_component, _key2, item) {
      return item;
    }
  ];

  // app/components/TodoApp.js
  var TodoApp = class extends BaseComponent {
    _name = "TodoApp";
    text = "";
    items = [];
    handleSubmit(event) {
      event.preventDefault();
      if (strlen(this.text) == 0) {
        return;
      }
      this.items.push(this.text);
      this.text = "";
    }
  };
  var TodoApp_x = [
    function(_component) {
      return function(event) {
        _component.handleSubmit(event);
      };
    },
    function(_component) {
      return _component.text;
    },
    function(_component) {
      return "\n        Add #" + (count(_component.items) + 1) + "\n    ";
    },
    function(_component) {
      return _component.items;
    }
  ];

  // app/components/TodoList.js
  var TodoList = class extends BaseComponent {
    _name = "TodoList";
    items = null;
  };
  var TodoList_x = [
    function(_component) {
      return _component.items;
    },
    function(_component, _key1, item) {
      return item;
    }
  ];

  // app/components/index.js
  var components = {
    CounterReducer,
    TodoReducer,
    MenuBar,
    Counter_x,
    Counter,
    HomePage_x,
    HomePage,
    Layout_x,
    Layout,
    NotFoundPage,
    CounterPage,
    TodoAppPage,
    StatefulCounter_x,
    StatefulCounter,
    StatefulTodoApp_x,
    StatefulTodoApp,
    TestComponent_x,
    TestComponent,
    TodoApp_x,
    TodoApp,
    TodoList_x,
    TodoList
  };

  // viewi/core/anchor.ts
  var anchorId = 0;
  var anchors = {};
  function getAnchor(target) {
    if (!target.__aid) {
      target.__aid = ++anchorId;
      anchors[target.__aid] = { current: -1, target, invalid: [], added: 0 };
    }
    return anchors[target.__aid];
  }
  function createAnchorNode(target, insert = false, anchor, name) {
    const anchorNode = document.createTextNode("");
    anchorNode._anchor = name ?? "#";
    if (anchor) {
      anchor.current++;
    }
    insert || anchor && target.childNodes.length > anchor.current ? (anchor ? target : target.parentElement).insertBefore(anchorNode, anchor ? target.childNodes[anchor.current] : target) : target.appendChild(anchorNode);
    return anchorNode;
  }

  // viewi/core/makeProxy.ts
  function makeProxy(component) {
    const proxy = new Proxy(component, {
      set(obj, prop, value) {
        var react = obj[prop] !== value;
        var ret = Reflect.set(obj, prop, value);
        if (react && prop in obj.$$r) {
          for (let i in obj.$$r[prop]) {
            const callbackFunc = obj.$$r[prop][i];
            callbackFunc[0].apply(null, callbackFunc[1]);
          }
        }
        return ret;
      }
    });
    component.$ = component;
    return proxy;
  }

  // viewi/core/hydrateComment.ts
  function hydrateComment(target, content) {
    const anchor = getAnchor(target);
    const max = target.childNodes.length;
    let end = anchor.current + 3;
    end = end > max ? max : end;
    const invalid = [];
    for (let i = anchor.current + 1; i < end; i++) {
      const potentialNode = target.childNodes[i];
      if (potentialNode.nodeType === 8) {
        anchor.current = i;
        anchor.invalid = anchor.invalid.concat(invalid);
        return potentialNode;
      }
      invalid.push(i);
    }
    anchor.added++;
    anchor.invalid = anchor.invalid.concat(invalid);
    console.log("Hydrate comment not found", content);
    const element = document.createComment(content);
    anchor.current = anchor.current + invalid.length + 1;
    return max > anchor.current ? target.insertBefore(element, target.childNodes[anchor.current]) : target.appendChild(element);
  }

  // viewi/core/hydrateTag.ts
  function hydrateTag(target, tag) {
    const anchor = getAnchor(target);
    const max = target.childNodes.length;
    let end = anchor.current + 3;
    end = end > max ? max : end;
    const invalid = [];
    for (let i = anchor.current + 1; i < end; i++) {
      const potentialNode = target.childNodes[i];
      if (potentialNode.nodeType === 1 && potentialNode.nodeName.toLowerCase() === tag) {
        anchor.current = i;
        anchor.invalid = anchor.invalid.concat(invalid);
        return potentialNode;
      }
      invalid.push(i);
    }
    anchor.added++;
    anchor.invalid = anchor.invalid.concat(invalid);
    console.warn("Hydrate not found", tag);
    const element = document.createElement(tag);
    anchor.current = anchor.current + invalid.length + 1;
    return max > anchor.current ? target.insertBefore(element, target.childNodes[anchor.current]) : target.appendChild(element);
  }

  // viewi/core/renderText.ts
  function renderText(instance, node, textNode, scope) {
    let callArguments = [instance];
    if (scope) {
      callArguments = callArguments.concat(scope.arguments);
    }
    const content = node.expression ? instance.$$t[node.code].apply(null, callArguments) : node.content ?? "";
    textNode.nodeValue !== content && (textNode.nodeValue = content);
  }

  // viewi/core/hydrateText.ts
  function hydrateText(target, instance, node, scope) {
    const anchor = getAnchor(target);
    const max = target.childNodes.length;
    let end = anchor.current + 3;
    end = end > max ? max : end;
    const invalid = [];
    const start = anchor.current > -1 ? anchor.current : anchor.current + 1;
    for (let i = start; i < end; i++) {
      const potentialNode = target.childNodes[i];
      if (potentialNode.nodeType === 3) {
        if (i === anchor.current) {
          break;
        }
        anchor.current = i;
        anchor.invalid = anchor.invalid.concat(invalid);
        renderText(instance, node, potentialNode, scope);
        return potentialNode;
      }
      i !== anchor.current && invalid.push(i);
    }
    anchor.added++;
    anchor.invalid = anchor.invalid.concat(invalid);
    const textNode = document.createTextNode("");
    renderText(instance, node, textNode, scope);
    anchor.current = anchor.current + invalid.length + 1;
    return max > anchor.current ? target.insertBefore(textNode, target.childNodes[anchor.current]) : target.appendChild(textNode);
  }

  // viewi/core/renderAttributeValue.ts
  function renderAttributeValue(instance, attribute, element, attrName, scope) {
    let valueContent = null;
    if (attribute.children) {
      valueContent = "";
      for (let av = 0; av < attribute.children.length; av++) {
        const attributeValue = attribute.children[av];
        let callArguments = [instance];
        if (scope) {
          callArguments = callArguments.concat(scope.arguments);
        }
        const childContent = attributeValue.expression ? instance.$$t[attributeValue.code].apply(null, callArguments) : attributeValue.content ?? "";
        valueContent = av === 0 ? childContent : valueContent + (childContent ?? "");
      }
    }
    if (valueContent !== null) {
      valueContent !== element.getAttribute(attrName) && element.setAttribute(attrName, valueContent);
    } else {
      element.removeAttribute(attrName);
    }
  }

  // viewi/core/renderForeach.ts
  function renderForeach(instance, node, directive, anchorNode, currentArrayScope, localDirectiveMap, scope) {
    let callArguments = [instance];
    if (scope) {
      callArguments = callArguments.concat(scope.arguments);
    }
    const data = instance.$$t[directive.children[0].forData].apply(null, callArguments);
    const isNumeric = Array.isArray(data);
    let insertTarget = anchorNode;
    let between = false;
    const usedMap = {};
    const deleteMap = {};
    for (let forKey in data) {
      const dataKey = isNumeric ? +forKey : forKey;
      const dataItem = data[dataKey];
      let nextScope = scope ? { map: { ...scope.map }, arguments: [...scope.arguments] } : { map: {}, arguments: [] };
      let found = false;
      for (let di in currentArrayScope) {
        if (currentArrayScope[di] === dataItem) {
          found = true;
          between = false;
          insertTarget = anchorNode;
          break;
        } else if (!between && !(dataKey in usedMap)) {
          insertTarget = currentArrayScope[di].begin;
          between = true;
        }
      }
      usedMap[dataKey] = true;
      if (!found) {
        nextScope.map[directive.children[0].forKey] = nextScope.arguments.length;
        nextScope.arguments.push(dataKey);
        nextScope.map[directive.children[0].forItem] = nextScope.arguments.length;
        nextScope.arguments.push(dataItem);
        const nextDirectives = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };
        const itemBeginAnchor = createAnchorNode(insertTarget, true, void 0, "b" /* BeginAnchor */);
        render(insertTarget, instance, [node], nextDirectives, false, true, nextScope);
        const itemEndAnchor = createAnchorNode(insertTarget, true, void 0, "e" /* EndAnchor */);
        if (dataKey in currentArrayScope) {
          deleteMap[dataKey] = currentArrayScope[dataKey];
        }
        currentArrayScope[dataKey] = {
          key: dataKey,
          value: dataItem,
          begin: itemBeginAnchor,
          end: itemEndAnchor
        };
      }
    }
    for (let di in currentArrayScope) {
      if (!(di in usedMap)) {
        while (currentArrayScope[di].end.previousSibling._anchor !== "b" /* BeginAnchor */) {
          currentArrayScope[di].end.previousSibling.remove();
        }
        currentArrayScope[di].begin.remove();
        currentArrayScope[di].end.remove();
        delete currentArrayScope[di];
      }
    }
    for (let di in deleteMap) {
      while (deleteMap[di].end.previousSibling._anchor !== "b" /* BeginAnchor */) {
        deleteMap[di].end.previousSibling.remove();
      }
      deleteMap[di].begin.remove();
      deleteMap[di].end.remove();
    }
  }

  // viewi/core/renderIf.ts
  function renderIf(instance, node, directive, anchorNode, ifConditions, localDirectiveMap, index) {
    let nextValue = true;
    for (let i = 0; i < index; i++) {
      nextValue = nextValue && !ifConditions.values[i];
    }
    if (directive.children) {
      nextValue = nextValue && !!instance.$$t[directive.children[0].code](instance);
    }
    if (ifConditions.values[index] !== nextValue) {
      ifConditions.values[index] = nextValue;
      if (nextValue) {
        render(anchorNode, instance, [node], { ...localDirectiveMap }, false, true);
      } else {
        while (!anchorNode.previousSibling._anchor) {
          anchorNode.previousSibling.remove();
        }
      }
    }
  }

  // viewi/core/updateComment.ts
  function updateComment(instance, node, commentNode) {
    const content = node.expression ? instance.$$t[node.code](instance) : node.content ?? "";
    commentNode.nodeValue !== content && (commentNode.nodeValue = content);
  }

  // viewi/core/render.ts
  function render(target, instance, nodes, directives, hydrate = true, insert = false, scope) {
    let ifConditions = null;
    let nextInsert = false;
    for (let i = 0; i < nodes.length; i++) {
      const node = nodes[i];
      let element = target;
      let breakAndContinue = false;
      let withAttributes = false;
      switch (node.type) {
        case "tag": {
          if (node.directives) {
            const localDirectiveMap = directives || { map: {}, storage: {} };
            let callArguments = [instance];
            if (scope) {
              callArguments = callArguments.concat(scope.arguments);
            }
            for (let d = 0; d < node.directives.length; d++) {
              const directive = node.directives[d];
              if (d in localDirectiveMap.map) {
                continue;
              }
              localDirectiveMap.map[d] = true;
              switch (directive.content) {
                case "if": {
                  ifConditions = { values: [], index: 0, subs: [] };
                  const nextValue = !!instance.$$t[directive.children[0].code].apply(null, callArguments);
                  ifConditions.values.push(nextValue);
                  const anchor = getAnchor(target);
                  createAnchorNode(target, false, anchor);
                  if (nextValue) {
                    render(target, instance, [node], localDirectiveMap, hydrate, insert, scope);
                  }
                  const anchorNode = createAnchorNode(target, false, anchor);
                  if (directive.children[0].subs) {
                    for (let subI in directive.children[0].subs) {
                      const trackingPath = directive.children[0].subs[subI];
                      ifConditions.subs.push(trackingPath);
                      if (!instance.$$r[trackingPath]) {
                        instance.$$r[trackingPath] = [];
                      }
                      instance.$$r[trackingPath].push([renderIf, [instance, node, directive, anchorNode, ifConditions, { ...localDirectiveMap }, ifConditions.index]]);
                    }
                  }
                  ifConditions.index++;
                  breakAndContinue = true;
                  break;
                }
                case "else-if": {
                  if (ifConditions) {
                    let nextValue = true;
                    for (let ifv = 0; ifv < ifConditions.index; ifv++) {
                      nextValue = nextValue && !ifConditions.values[ifv];
                    }
                    nextValue = nextValue && !ifConditions.values[ifConditions.index - 1] && !!instance.$$t[directive.children[0].code].apply(null, callArguments);
                    ifConditions.values.push(nextValue);
                    const anchor = getAnchor(target);
                    createAnchorNode(target, false, anchor);
                    if (nextValue) {
                      render(target, instance, [node], localDirectiveMap, hydrate, insert, scope);
                    }
                    const anchorNode = createAnchorNode(target, false, anchor);
                    if (directive.children[0].subs) {
                      ifConditions.subs = ifConditions.subs.concat(directive.children[0].subs);
                    }
                    for (let subI in ifConditions.subs) {
                      const trackingPath = ifConditions.subs[subI];
                      if (!instance.$$r[trackingPath]) {
                        instance.$$r[trackingPath] = [];
                      }
                      instance.$$r[trackingPath].push([renderIf, [instance, node, directive, anchorNode, ifConditions, { ...localDirectiveMap }, ifConditions.index]]);
                    }
                    ifConditions.index++;
                    breakAndContinue = true;
                  } else {
                    console.warn("Directive else-if has missing previous if/else-if", directive.content, directive);
                  }
                  break;
                }
                case "else": {
                  if (ifConditions) {
                    let nextValue = true;
                    for (let ifv = 0; ifv < ifConditions.index; ifv++) {
                      nextValue = nextValue && !ifConditions.values[ifv];
                    }
                    ifConditions.values.push(nextValue);
                    const anchor = getAnchor(target);
                    createAnchorNode(target, false, anchor);
                    if (nextValue) {
                      render(target, instance, [node], localDirectiveMap, hydrate, insert, scope);
                    }
                    const anchorNode = createAnchorNode(target, false, anchor);
                    for (let subI in ifConditions.subs) {
                      const trackingPath = ifConditions.subs[subI];
                      if (!instance.$$r[trackingPath]) {
                        instance.$$r[trackingPath] = [];
                      }
                      instance.$$r[trackingPath].push([renderIf, [instance, node, directive, anchorNode, ifConditions, { ...localDirectiveMap }, ifConditions.index]]);
                    }
                    ifConditions.index++;
                    breakAndContinue = true;
                  } else {
                    console.warn("Directive else has missing previous if/else-if", directive.content, directive);
                  }
                  break;
                }
                case "foreach": {
                  const data = instance.$$t[directive.children[0].forData].apply(null, callArguments);
                  const anchor = getAnchor(target);
                  createAnchorNode(target, false, anchor);
                  const isNumeric = Array.isArray(data);
                  const dataArrayScope = {};
                  for (let forKey in data) {
                    const dataKey = isNumeric ? +forKey : forKey;
                    const dataItem = data[dataKey];
                    let nextScope = scope ? { map: { ...scope.map }, arguments: [...scope.arguments] } : { map: {}, arguments: [] };
                    nextScope.map[directive.children[0].forKey] = nextScope.arguments.length;
                    nextScope.arguments.push(dataKey);
                    nextScope.map[directive.children[0].forItem] = nextScope.arguments.length;
                    nextScope.arguments.push(dataItem);
                    const nextDirectives = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };
                    const itemBeginAnchor = createAnchorNode(target, false, anchor, "b" /* BeginAnchor */);
                    render(target, instance, [node], nextDirectives, hydrate, insert, nextScope);
                    const itemEndAnchor = createAnchorNode(target, false, anchor, "e" /* EndAnchor */);
                    dataArrayScope[dataKey] = {
                      key: dataKey,
                      value: dataItem,
                      begin: itemBeginAnchor,
                      end: itemEndAnchor
                    };
                  }
                  const anchorNode = createAnchorNode(target, false, anchor);
                  if (directive.children[0].subs) {
                    for (let subI in directive.children[0].subs) {
                      const trackingPath = directive.children[0].subs[subI];
                      if (!instance.$$r[trackingPath]) {
                        instance.$$r[trackingPath] = [];
                      }
                      const nextDirectives = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };
                      instance.$$r[trackingPath].push(
                        [
                          renderForeach,
                          [instance, node, directive, anchorNode, dataArrayScope, nextDirectives, scope]
                        ]
                      );
                    }
                  }
                  breakAndContinue = true;
                  break;
                }
                default: {
                  console.warn("Directive not implemented", directive.content, directive);
                  breakAndContinue = true;
                  break;
                }
              }
              if (breakAndContinue) {
                break;
              }
            }
            if (breakAndContinue) {
              continue;
            }
          }
          if (node.content === "template") {
            nextInsert = insert;
            break;
          }
          withAttributes = true;
          const content = node.expression ? instance.$$t[node.code](instance) : node.content ?? "";
          element = hydrate ? hydrateTag(target, content) : insert ? target.parentElement.insertBefore(document.createElement(content), target) : target.appendChild(document.createElement(content));
          break;
        }
        case "text": {
          let textNode;
          if (hydrate) {
            textNode = hydrateText(target, instance, node, scope);
          } else {
            textNode = document.createTextNode("");
            renderText(instance, node, textNode, scope);
            insert ? target.parentElement.insertBefore(textNode, target) : target.appendChild(textNode);
          }
          if (node.subs) {
            for (let subI in node.subs) {
              const trackingPath = node.subs[subI];
              if (!instance.$$r[trackingPath]) {
                instance.$$r[trackingPath] = [];
              }
              instance.$$r[trackingPath].push([renderText, [instance, node, textNode, scope]]);
            }
          }
          break;
        }
        case "comment": {
          const content = node.expression ? instance.$$t[node.code](instance) : node.content ?? "";
          const commentNode = hydrate ? hydrateComment(target, content) : insert ? target.parentElement.insertBefore(document.createComment(content), target) : target.appendChild(document.createComment(content));
          if (node.subs) {
            for (let subI in node.subs) {
              const trackingPath = node.subs[subI];
              if (!instance.$$r[trackingPath]) {
                instance.$$r[trackingPath] = [];
              }
              instance.$$r[trackingPath].push([updateComment, [instance, node, commentNode]]);
            }
          }
          break;
        }
        default: {
          console.warn("Node type not implemented", node);
          break;
        }
      }
      if (withAttributes) {
        if (node.attributes) {
          const toRemove = hydrate ? element.getAttributeNames() : null;
          const hasMap = hydrate ? {} : null;
          for (let a in node.attributes) {
            const attribute = node.attributes[a];
            const attrName = attribute.expression ? instance.$$t[attribute.code](instance) : attribute.content ?? "";
            if (attrName[0] === "(") {
              const eventName = attrName.substring(1, attrName.length - 1);
              if (attribute.children) {
                const eventHandler = instance.$$t[attribute.dynamic ? attribute.dynamic.code : attribute.children[0].code](instance);
                element.addEventListener(eventName, eventHandler);
              }
            } else {
              hydrate && (hasMap[attrName] = true);
              renderAttributeValue(instance, attribute, element, attrName, scope);
              let valueSubs = [];
              if (attribute.children) {
                for (let av in attribute.children) {
                  const attributeValue = attribute.children[av];
                  if (attributeValue.subs) {
                    valueSubs = valueSubs.concat(attributeValue.subs);
                  }
                }
              }
              if (valueSubs) {
                for (let subI in valueSubs) {
                  const trackingPath = valueSubs[subI];
                  if (!instance.$$r[trackingPath]) {
                    instance.$$r[trackingPath] = [];
                  }
                  instance.$$r[trackingPath].push([renderAttributeValue, [instance, attribute, element, attrName, scope]]);
                }
              }
            }
          }
          if (hydrate) {
            for (let ai = 0; ai < toRemove.length; ai++) {
              if (!(toRemove[ai] in hasMap)) {
                element.removeAttribute(toRemove[ai]);
              }
            }
          }
        } else if (hydrate) {
          const toRemove = element.getAttributeNames();
          for (let ai = 0; ai < toRemove.length; ai++) {
            element.removeAttribute(toRemove[ai]);
          }
        }
      }
      if (node.children) {
        render(element, instance, node.children, void 0, hydrate, nextInsert, scope);
      }
    }
  }

  // viewi/core/unpack.ts
  function unpack(item) {
    let nodeType = "value";
    switch (item.t) {
      case "t": {
        nodeType = "tag";
        break;
      }
      case "a": {
        nodeType = "attr";
        break;
      }
      case void 0:
      case "v": {
        nodeType = "value";
        break;
      }
      case "c": {
        nodeType = "component";
        break;
      }
      case "x": {
        nodeType = "text";
        break;
      }
      case "m": {
        nodeType = "comment";
        break;
      }
      case "r": {
        nodeType = "root";
        break;
      }
      default:
        throw new Error("Type " + item.t + " is not defined in build");
    }
    item.type = nodeType;
    delete item.t;
    if (item.c) {
      item.content = item.c;
      delete item.c;
    }
    if (item.e) {
      item.expression = item.e;
      delete item.e;
    }
    if (item.a) {
      item.attributes = item.a;
      delete item.a;
      for (let i in item.attributes) {
        unpack(item.attributes[i]);
      }
    }
    if (item.i) {
      item.directives = item.i;
      delete item.i;
      for (let i in item.directives) {
        unpack(item.directives[i]);
      }
    }
    if (item.h) {
      item.children = item.h;
      delete item.h;
      for (let i in item.children) {
        unpack(item.children[i]);
      }
    }
    ;
  }

  // viewi/index.ts
  var componentsMeta = {};
  var Viewi = () => ({
    version: "2.0.1"
  });
  globalThis.Viewi = Viewi;
  console.log("Viewi entry");
  var counterTarget = document.getElementById("counter");
  function renderComponent(name) {
    if (!(name in componentsMeta)) {
      throw new Error(`Component ${name} not found.`);
    }
    if (!(name in components)) {
      throw new Error(`Component ${name} not found.`);
    }
    const root = componentsMeta[name].nodes;
    const instance = makeProxy(new components[name]());
    const inlineExpressions = name + "_x";
    if (inlineExpressions in components) {
      instance.$$t = components[inlineExpressions];
    }
    if (counterTarget && root) {
      if (!root.unpacked) {
        unpack(root);
        root.unpacked = true;
      }
      const rootChildren = root.children;
      rootChildren && render(counterTarget, instance, rootChildren);
    }
    for (let a in anchors) {
      const anchor = anchors[a];
      for (let i = anchor.target.childNodes.length - 1; i >= anchor.current + 1; i--) {
        anchor.target.childNodes[i].remove();
      }
      for (let i = anchor.invalid.length - 1; i >= 0; i--) {
        anchor.target.childNodes[anchor.invalid[i]].remove();
      }
    }
  }
  (async () => {
    componentsMeta = await (await fetch("/assets/components.json")).json();
    setTimeout(() => renderComponent("TestComponent"), 500);
  })();
})();
