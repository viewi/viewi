(() => {
  // viewi/core/anchor.ts
  var anchorId = 0;
  var anchorNodeId = 0;
  var anchors = {};
  function getAnchor(target) {
    if (!target.__aid) {
      target.__aid = ++anchorId;
      anchors[target.__aid] = { current: -1, target, invalid: [], added: 0 };
    }
    return anchors[target.__aid];
  }
  function nextAnchorNodeId() {
    return ++anchorNodeId;
  }
  function createAnchorNode(target, insert = false, anchor, name) {
    const anchorNode = document.createTextNode("");
    anchorNode._anchor = name ?? "#" + ++anchorNodeId;
    if (anchor) {
      anchor.current++;
    }
    insert || anchor && target.childNodes.length > anchor.current ? (anchor ? target : target.parentElement).insertBefore(anchorNode, anchor ? target.childNodes[anchor.current] : target) : target.appendChild(anchorNode);
    return anchorNode;
  }

  // viewi/core/componentsMeta.ts
  var componentsMeta = { list: {} };
  var componentsMeta_default = componentsMeta;

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
      return "\n    Count " + (_component.count ?? "") + " " + (strlen(_component.message) ?? "") + "\n";
    },
    function(_component) {
      return "\nCount " + (_component.count ?? "") + " " + (strlen(_component.message) ?? "") + "\n";
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
      return "\n        " + (_component.title ?? "") + " | Viewi\n    ";
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

  // app/components/ItemComponent.js
  var ItemComponent = class extends BaseComponent {
    _name = "ItemComponent";
  };

  // app/components/SomeComponent.js
  var SomeComponent = class extends BaseComponent {
    _name = "SomeComponent";
  };

  // app/components/TestButton.js
  var TestButton = class extends BaseComponent {
    _name = "TestButton";
    id = null;
    title = null;
    class = null;
  };
  var TestButton_x = [
    function(_component) {
      return _component.id;
    },
    function(_component) {
      return _component.title;
    },
    function(_component) {
      return _component.class;
    },
    function(_component) {
      return " " + (_component.title ?? "") + "\n";
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
    nestedIf = true;
    dynamic = "div";
    dynamic2 = "ItemComponent";
    getName(name) {
      var sum = (1 + 5) * 10;
      return name ?? "DefaultName";
    }
    addTodo() {
      this.arrNested = { "a": { "a": "Apple", "b": "Orange", "c": "Lemon" }, "d": { "R": "Rat", "T": "Dog", "G": "Cat" }, "b": { "a": "Apple", "b": "Orange", "c": "Lemon" } };
    }
    onEvent(event) {
      event.preventDefault();
    }
    toggleIf() {
      this.ifValue = !this.ifValue;
      this.arr = this.ifValue ? ["a", "b", "c"] : ["x", "b", "r"];
    }
    toggleElseIf() {
      this.ifElseValue = !this.ifElseValue;
    }
  };
  var TestComponent_x = [
    function(_component) {
      return "Tag test " + (_component.name ?? "") + " " + (_component._name2_Test ?? "");
    },
    function(_component) {
      return "\n    $notAVar " + (_component.getName() ?? "") + " " + (_component.getName(_component.name) ?? "") + "\n    Nested\n    ";
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
      return _component.nestedIf;
    },
    function(_component) {
      return _component.name;
    },
    function(_component) {
      return "Custom " + (_component.name ?? "");
    },
    function(_component) {
      return _component.nestedIf;
    },
    function(_component) {
      return { "id": "myid", "title": "Custom " + _component.name, "class": "mui-btn--accent" };
    },
    function(_component) {
      return "\n    Custom " + (_component.name ?? "") + "\n";
    },
    function(_component) {
      return function(event) {
        _component.name = "Viewi Junior";
      };
    },
    function(_component) {
      return function(event) {
        _component.nestedIf = !_component.nestedIf;
      };
    },
    function(_component) {
      return _component.nestedIf;
    },
    function(_component) {
      return "Custom " + (_component.name ?? "") + " Slot";
    },
    function(_component) {
      return _component.nestedIf;
    },
    function(_component) {
      return _component.arrNested;
    },
    function(_component, key, subArr) {
      return "\n    Custom " + (_component.name ?? "") + " Slot\n    ";
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
      return key + ". " + (subKey ?? "") + ". " + (subItem ?? "");
    },
    function(_component, key, subArr) {
      return _component.nestedIf;
    },
    function(_component, key, subArr) {
      return _component.name;
    },
    function(_component) {
      return function(event) {
        _component.nestedIf = !_component.nestedIf;
      };
    },
    function(_component) {
      return function(event) {
        _component.dynamic = _component.dynamic === "div" ? "ItemComponent" : "div";
      };
    },
    function(_component) {
      return "\n" + (_component.dynamic ?? "") + " " + (_component.dynamic2 ?? "") + "\n";
    },
    function(_component) {
      return _component.dynamic;
    },
    function(_component) {
      return "Tag or component " + (_component.dynamic ?? "") + " " + (_component.dynamic2 ?? "");
    },
    function(_component) {
      return _component.dynamic2;
    },
    function(_component) {
      return "Tag or component " + (_component.dynamic ?? "") + " " + (_component.dynamic2 ?? "");
    },
    function(_component) {
      return "Custom " + (_component.name ?? "") + " Slot";
    },
    function(_component) {
      return "Custom " + (_component.name ?? "") + " slot\n        ";
    },
    function(_component) {
      return "Custom header " + (_component.name ?? "") + " inside div";
    },
    function(_component) {
      return "Custom " + (_component.name ?? "") + " footer";
    },
    function(_component) {
      return _component.addTodo.bind(_component);
    },
    function(_component) {
      return _component.nestedIf;
    },
    function(_component) {
      return _component.name;
    },
    function(_component) {
      return _component.ifValue;
    },
    function(_component) {
      return _component.arrNested;
    },
    function(_component, key, subArr) {
      return subArr;
    },
    function(_component, key, subArr, subKey, subItem) {
      return key + ". " + (subKey ?? "") + ". " + (subItem ?? "");
    },
    function(_component) {
      return _component.arr;
    },
    function(_component, _key1, item) {
      return _component.ifElseValue;
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
    function(_component, _key1, item) {
      return _component.nestedIf;
    },
    function(_component, _key1, item) {
      return _component.name;
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
      return _component.ifValue;
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
      return key + ". " + (subKey ?? "") + ". " + (subItem ?? "");
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
      return key + ". " + (subKey ?? "") + ". " + (subItem ?? "");
    },
    function(_component) {
      return _component.toggleIf.bind(_component);
    },
    function(_component) {
      return _component.toggleElseIf.bind(_component);
    },
    function(_component) {
      return function(event) {
        _component.nestedIf = !_component.nestedIf;
      };
    },
    function(_component) {
      return function(event) {
        _component.name = "Viewi Junior";
      };
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
    ItemComponent,
    SomeComponent,
    TestButton_x,
    TestButton,
    TestComponent_x,
    TestComponent,
    TodoApp_x,
    TodoApp,
    TodoList_x,
    TodoList
  };

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
    const content = (node.expression ? instance.$$t[node.code].apply(null, callArguments) : node.content) ?? "";
    textNode.nodeValue !== content && (textNode.nodeValue = content);
    if (textNode.parentNode && !document.body.contains(textNode)) {
      console.log("Element is missing from the page", textNode);
    }
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
        if (scope.arguments) {
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

  // viewi/core/dispose.ts
  function dispose(scope) {
    for (let reactivityIndex in scope.track) {
      const reactivityItem = scope.track[reactivityIndex];
      delete scope.instance.$$r[reactivityItem.path][reactivityItem.id];
    }
    scope.track = [];
    scope.components = [];
    if (scope.children) {
      for (let i in scope.children) {
        dispose(scope.children[i]);
      }
      scope.children = {};
    }
    if (scope.parent) {
      delete scope.parent.children[scope.id];
      delete scope.parent;
    }
  }

  // viewi/core/renderForeach.ts
  function renderForeach(instance, node, directive, anchorNode, currentArrayScope, localDirectiveMap, scope) {
    let callArguments = [instance];
    if (scope.arguments) {
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
      const scopeId = ++scope.counter;
      const nextScope = {
        id: scopeId,
        instance,
        arguments: [...scope.arguments],
        components: [],
        map: { ...scope.map },
        track: [],
        parent: scope,
        children: {},
        counter: 0
      };
      scope.children[scopeId] = nextScope;
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
        const itemBeginAnchor = createAnchorNode(insertTarget, true, void 0, "b" /* BeginAnchor */ + nextAnchorNodeId());
        render(insertTarget, instance, [node], nextScope, nextDirectives, false, true);
        const itemEndAnchor = createAnchorNode(insertTarget, true, void 0, itemBeginAnchor._anchor);
        if (dataKey in currentArrayScope) {
          deleteMap[dataKey] = currentArrayScope[dataKey];
        }
        currentArrayScope[dataKey] = {
          key: dataKey,
          value: dataItem,
          begin: itemBeginAnchor,
          end: itemEndAnchor,
          scope: nextScope
        };
      }
    }
    for (let di in currentArrayScope) {
      if (!(di in usedMap)) {
        const endAnchor = currentArrayScope[di].end;
        while (endAnchor.previousSibling._anchor !== endAnchor._anchor) {
          endAnchor.previousSibling.remove();
        }
        currentArrayScope[di].begin.remove();
        endAnchor.remove();
        dispose(currentArrayScope[di].scope);
        delete currentArrayScope[di];
      }
    }
    for (let di in deleteMap) {
      const endAnchor = deleteMap[di].end;
      while (endAnchor.previousSibling._anchor !== endAnchor._anchor) {
        endAnchor.previousSibling.remove();
      }
      deleteMap[di].begin.remove();
      dispose(deleteMap[di].scope);
      endAnchor.remove();
    }
  }

  // viewi/core/renderIf.ts
  function renderIf(instance, node, scopeContainer, directive, ifConditions, localDirectiveMap, index) {
    let nextValue = true;
    for (let i = 0; i < index; i++) {
      nextValue = nextValue && !ifConditions.values[i];
    }
    if (directive.children) {
      nextValue = nextValue && !!instance.$$t[directive.children[0].code](instance);
    }
    const anchorNode = scopeContainer.anchorNode;
    const nextDirectives = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };
    if (ifConditions.values[index] !== nextValue) {
      const scope = scopeContainer.scope.parent;
      ifConditions.values[index] = nextValue;
      if (nextValue) {
        const scopeId = ++scope.counter;
        const nextScope = {
          id: scopeId,
          instance,
          arguments: [...scope.arguments],
          components: [],
          map: { ...scope.map },
          track: [],
          parent: scope,
          children: {},
          counter: 0
        };
        scopeContainer.scope = nextScope;
        scope.children[scopeId] = nextScope;
        render(anchorNode, instance, [node], nextScope, nextDirectives, false, true);
      } else {
        dispose(scopeContainer.scope);
        scopeContainer.scope = {
          id: -1,
          instance,
          arguments: [],
          components: [],
          map: {},
          track: [],
          parent: scope,
          children: {},
          counter: 0
        };
        while (anchorNode.previousSibling._anchor !== anchorNode._anchor) {
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

  // viewi/core/track.ts
  var trackingId = 0;
  function track(instance, trackingPath, scope, action) {
    if (!instance.$$r[trackingPath]) {
      instance.$$r[trackingPath] = {};
    }
    const trackId = ++trackingId;
    scope.track.push({ id: trackId, path: trackingPath });
    instance.$$r[trackingPath][trackId] = action;
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

  // viewi/core/renderDynamic.ts
  function renderDynamic(instance, node, scopeContainer) {
    const content = node.expression ? instance.$$t[node.code](instance) : node.content ?? "";
    const componentTag = node.type === "component" || node.expression && isComponent(content);
    const anchorNode = scopeContainer.anchorNode;
    const scope = scopeContainer.scope.parent;
    dispose(scopeContainer.scope);
    while (anchorNode.previousSibling._anchor !== anchorNode._anchor) {
      anchorNode.previousSibling.remove();
    }
    const scopeId = ++scope.counter;
    const nextScope = {
      id: scopeId,
      arguments: [...scope.arguments],
      components: [],
      map: { ...scope.map },
      track: [],
      instance,
      parent: scope,
      children: {},
      counter: 0
    };
    scopeContainer.scope = nextScope;
    scope.children[scopeId] = nextScope;
    if (componentTag) {
      const slots = {};
      if (node.slots) {
        const scopeId2 = ++nextScope.counter;
        const slotScope = {
          id: scopeId2,
          arguments: [...scope.arguments],
          components: [],
          map: { ...scope.map },
          track: [],
          parent: nextScope,
          children: {},
          counter: 0,
          slots: scope.slots
        };
        for (let slotName in node.slots) {
          slots[slotName] = {
            node: node.slots[slotName],
            scope: slotScope
          };
        }
      }
      renderComponent(anchorNode, content, { attributes: node.attributes, scope, instance }, slots, false, true);
      return;
    } else {
      const element = anchorNode.parentElement.insertBefore(document.createElement(content), anchorNode);
      if (node.attributes) {
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
            renderAttributeValue(instance, attribute, element, attrName, nextScope);
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
                track(instance, trackingPath, nextScope, [renderAttributeValue, [instance, attribute, element, attrName, nextScope]]);
              }
            }
          }
        }
      }
      if (node.children) {
        render(element, instance, node.children, nextScope, void 0, false, false);
      }
    }
  }

  // viewi/core/render.ts
  function render(target, instance, nodes, scope, directives, hydrate = true, insert = false) {
    let ifConditions = null;
    let nextInsert = false;
    for (let i = 0; i < nodes.length; i++) {
      const node = nodes[i];
      let element = target;
      let breakAndContinue = false;
      let withAttributes = false;
      let childScope = scope;
      switch (node.type) {
        case "tag":
        case "component": {
          if (node.directives) {
            const localDirectiveMap = directives || { map: {}, storage: {} };
            let callArguments = [instance];
            if (scope.arguments) {
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
                  const anchor2 = hydrate ? getAnchor(target) : void 0;
                  const anchorBegin2 = createAnchorNode(target, insert, anchor2);
                  const nextDirectives = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };
                  const scopeId = ++scope.counter;
                  const nextScope2 = {
                    id: scopeId,
                    arguments: scope.arguments,
                    components: [],
                    map: scope.map,
                    instance,
                    track: [],
                    parent: scope,
                    children: {},
                    counter: 0
                  };
                  scope.children[scopeId] = nextScope2;
                  if (nextValue) {
                    render(target, instance, [node], nextScope2, localDirectiveMap, hydrate, insert);
                  }
                  const anchorNode = createAnchorNode(target, insert, anchor2, anchorBegin2._anchor);
                  if (directive.children[0].subs) {
                    for (let subI in directive.children[0].subs) {
                      const trackingPath = directive.children[0].subs[subI];
                      ifConditions.subs.push(trackingPath);
                      track(instance, trackingPath, scope, [renderIf, [instance, node, { scope: nextScope2, anchorNode }, directive, ifConditions, nextDirectives, ifConditions.index]]);
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
                    const anchor2 = hydrate ? getAnchor(target) : void 0;
                    const anchorBegin2 = createAnchorNode(target, insert, anchor2);
                    const nextDirectives = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };
                    const scopeId = ++scope.counter;
                    const nextScope2 = {
                      id: scopeId,
                      instance,
                      arguments: scope.arguments,
                      components: [],
                      map: scope.map,
                      track: [],
                      parent: scope,
                      children: {},
                      counter: 0
                    };
                    scope.children[scopeId] = nextScope2;
                    if (nextValue) {
                      render(target, instance, [node], nextScope2, localDirectiveMap, hydrate, insert);
                    }
                    const anchorNode = createAnchorNode(target, insert, anchor2, anchorBegin2._anchor);
                    if (directive.children[0].subs) {
                      ifConditions.subs = ifConditions.subs.concat(directive.children[0].subs);
                    }
                    for (let subI in ifConditions.subs) {
                      const trackingPath = ifConditions.subs[subI];
                      track(instance, trackingPath, scope, [renderIf, [instance, node, { scope: nextScope2, anchorNode }, directive, ifConditions, nextDirectives, ifConditions.index]]);
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
                    const anchor2 = hydrate ? getAnchor(target) : void 0;
                    const anchorBegin2 = createAnchorNode(target, insert, anchor2);
                    const nextDirectives = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };
                    const scopeId = ++scope.counter;
                    const nextScope2 = {
                      id: scopeId,
                      instance,
                      arguments: scope.arguments,
                      components: [],
                      map: scope.map,
                      track: [],
                      parent: scope,
                      children: {},
                      counter: 0
                    };
                    scope.children[scopeId] = nextScope2;
                    if (nextValue) {
                      render(target, instance, [node], nextScope2, localDirectiveMap, hydrate, insert);
                    }
                    const anchorNode = createAnchorNode(target, insert, anchor2, anchorBegin2._anchor);
                    for (let subI in ifConditions.subs) {
                      const trackingPath = ifConditions.subs[subI];
                      track(instance, trackingPath, scope, [renderIf, [instance, node, { scope: nextScope2, anchorNode }, directive, ifConditions, nextDirectives, ifConditions.index]]);
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
                  const anchor2 = hydrate ? getAnchor(target) : void 0;
                  const anchorBegin2 = createAnchorNode(target, insert, anchor2);
                  const isNumeric = Array.isArray(data);
                  const dataArrayScope = {};
                  for (let forKey in data) {
                    const dataKey = isNumeric ? +forKey : forKey;
                    const dataItem = data[dataKey];
                    const scopeId = ++scope.counter;
                    const nextScope2 = {
                      id: scopeId,
                      instance,
                      arguments: [...scope.arguments],
                      components: [],
                      map: { ...scope.map },
                      track: [],
                      parent: scope,
                      children: {},
                      counter: 0
                    };
                    scope.children[scopeId] = nextScope2;
                    nextScope2.map[directive.children[0].forKey] = nextScope2.arguments.length;
                    nextScope2.arguments.push(dataKey);
                    nextScope2.map[directive.children[0].forItem] = nextScope2.arguments.length;
                    nextScope2.arguments.push(dataItem);
                    const nextDirectives = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };
                    const itemBeginAnchor = createAnchorNode(target, insert, anchor2, "b" /* BeginAnchor */ + nextAnchorNodeId());
                    render(target, instance, [node], nextScope2, nextDirectives, hydrate, insert);
                    const itemEndAnchor = createAnchorNode(target, insert, anchor2, itemBeginAnchor._anchor);
                    dataArrayScope[dataKey] = {
                      key: dataKey,
                      value: dataItem,
                      begin: itemBeginAnchor,
                      end: itemEndAnchor,
                      scope: nextScope2
                    };
                  }
                  const anchorNode = createAnchorNode(target, insert, anchor2, anchorBegin2._anchor);
                  if (directive.children[0].subs) {
                    for (let subI in directive.children[0].subs) {
                      const trackingPath = directive.children[0].subs[subI];
                      const nextDirectives = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };
                      track(instance, trackingPath, scope, [
                        renderForeach,
                        [instance, node, directive, anchorNode, dataArrayScope, nextDirectives, scope]
                      ]);
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
          const content = node.expression ? instance.$$t[node.code](instance) : node.content ?? "";
          const isDynamic = node.subs;
          const componentTag = node.type === "component" || node.expression && isComponent(content);
          let anchor;
          let anchorBegin;
          let nextScope;
          if (isDynamic) {
            anchor = hydrate ? getAnchor(target) : void 0;
            anchorBegin = createAnchorNode(target, insert, anchor);
          }
          if (isDynamic || componentTag) {
            const scopeId = ++scope.counter;
            nextScope = {
              id: scopeId,
              arguments: [...scope.arguments],
              components: [],
              map: { ...scope.map },
              track: [],
              instance,
              parent: scope,
              children: {},
              counter: 0
            };
            scope.children[scopeId] = nextScope;
            childScope = nextScope;
          }
          if (componentTag) {
            const slots = {};
            if (node.slots) {
              const scopeId = ++nextScope.counter;
              const slotScope = {
                id: scopeId,
                arguments: [...scope.arguments],
                components: [],
                map: { ...scope.map },
                track: [],
                parent: nextScope,
                instance,
                children: {},
                counter: 0,
                slots: scope.slots
              };
              nextScope.children[scopeId] = slotScope;
              for (let slotName in node.slots) {
                slots[slotName] = {
                  node: node.slots[slotName],
                  scope: slotScope
                };
              }
            }
            renderComponent(target, content, { attributes: node.attributes, scope }, slots, hydrate, insert);
          } else {
            if (node.content === "template") {
              nextInsert = insert;
              break;
            }
            if (node.content === "slot") {
              nextInsert = insert;
              let slotName = "default";
              if (node.attributes) {
                for (let attrIndex in node.attributes) {
                  if (node.attributes[attrIndex].content === "name") {
                    slotName = node.attributes[attrIndex].children[0].content;
                  }
                }
              }
              if (slotName in scope.slots) {
                const slot = scope.slots[slotName];
                if (!slot.node.unpacked) {
                  unpack(slot.node);
                  slot.node.unpacked = true;
                }
                render(element, slot.scope.instance, slot.node.children, slot.scope, void 0, hydrate, nextInsert);
              } else {
                if (node.children) {
                  render(element, instance, node.children, scope, void 0, hydrate, nextInsert);
                }
              }
              continue;
            }
            withAttributes = true;
            element = hydrate ? hydrateTag(target, content) : insert ? target.parentElement.insertBefore(document.createElement(content), target) : target.appendChild(document.createElement(content));
          }
          if (isDynamic) {
            const anchorNode = createAnchorNode(target, insert, anchor, anchorBegin._anchor);
            if (node.subs) {
              for (let subI in node.subs) {
                const trackingPath = node.subs[subI];
                track(instance, trackingPath, scope, [renderDynamic, [instance, node, { scope: nextScope, anchorNode }]]);
              }
            }
          }
          if (componentTag) {
            continue;
          }
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
              track(instance, trackingPath, scope, [renderText, [instance, node, textNode, scope]]);
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
              track(instance, trackingPath, scope, [updateComment, [instance, node, commentNode]]);
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
                  track(instance, trackingPath, scope, [renderAttributeValue, [instance, attribute, element, attrName, scope]]);
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
        render(element, instance, node.children, childScope, void 0, hydrate, nextInsert);
      }
    }
  }

  // viewi/core/updateProp.ts
  function updateProp(instance, attribute, props) {
    const parentInstance = props.scope.instance;
    const attrName = attribute.expression ? parentInstance.$$t[attribute.code](parentInstance) : attribute.content ?? "";
    if (attrName[0] === "(") {
    } else {
      let valueContent = null;
      let valueSubs = [];
      if (attribute.children) {
        for (let av = 0; av < attribute.children.length; av++) {
          const attributeValue = attribute.children[av];
          let callArguments = [parentInstance];
          if (props.scope.arguments) {
            callArguments = callArguments.concat(props.scope.arguments);
          }
          const childContent = attributeValue.expression ? parentInstance.$$t[attributeValue.code].apply(null, callArguments) : attributeValue.content ?? "";
          valueContent = av === 0 ? childContent : valueContent + (childContent ?? "");
          if (attributeValue.subs) {
            valueSubs = valueSubs.concat(attributeValue.subs);
          }
        }
      }
      if (attrName === "_props" && valueContent) {
        for (let propName in valueContent) {
          instance[propName] = valueContent[propName];
          instance._props[propName] = valueContent[propName];
        }
      } else {
        instance[attrName] = valueContent;
        instance._props[attrName] = valueContent;
      }
    }
  }

  // viewi/core/renderComponent.ts
  function renderComponent(target, name, props, slots, hydrate = false, insert = false) {
    if (!(name in componentsMeta_default.list)) {
      throw new Error(`Component ${name} not found.`);
    }
    if (!(name in components)) {
      throw new Error(`Component ${name} not found.`);
    }
    const root = componentsMeta_default.list[name].nodes;
    const instance = makeProxy(new components[name]());
    const inlineExpressions = name + "_x";
    if (inlineExpressions in components) {
      instance.$$t = components[inlineExpressions];
    }
    const scopeId = props ? ++props.scope.counter : 0;
    const scope = {
      id: scopeId,
      arguments: props ? [...props.scope.arguments] : [],
      components: [],
      instance,
      map: props ? { ...props.scope.map } : {},
      track: [],
      children: {},
      counter: 0,
      parent: props ? props.scope : void 0,
      slots
    };
    props && (props.scope.children[scopeId] = scope);
    if (props && props.attributes) {
      const parentInstance = props.scope.instance;
      for (let a in props.attributes) {
        const attribute = props.attributes[a];
        const attrName = attribute.expression ? parentInstance.$$t[attribute.code](parentInstance) : attribute.content ?? "";
        if (attrName[0] === "(") {
        } else {
          let valueContent = null;
          let valueSubs = [];
          if (attribute.children) {
            for (let av = 0; av < attribute.children.length; av++) {
              const attributeValue = attribute.children[av];
              let callArguments = [parentInstance];
              if (props.scope.arguments) {
                callArguments = callArguments.concat(props.scope.arguments);
              }
              const childContent = attributeValue.expression ? parentInstance.$$t[attributeValue.code].apply(null, callArguments) : attributeValue.content ?? "";
              valueContent = av === 0 ? childContent : valueContent + (childContent ?? "");
              if (attributeValue.subs) {
                valueSubs = valueSubs.concat(attributeValue.subs);
              }
            }
          }
          if (attrName === "_props" && valueContent) {
            for (let propName in valueContent) {
              instance[propName] = valueContent[propName];
              instance._props[propName] = valueContent[propName];
            }
          } else {
            instance[attrName] = valueContent;
            instance._props[attrName] = valueContent;
          }
          if (valueSubs) {
            for (let subI in valueSubs) {
              const trackingPath = valueSubs[subI];
              track(parentInstance, trackingPath, props.scope, [updateProp, [instance, attribute, props]]);
            }
          }
        }
      }
    }
    if (target && root) {
      if (!root.unpacked) {
        unpack(root);
        root.unpacked = true;
      }
      const rootChildren = root.children;
      rootChildren && render(target, instance, rootChildren, scope, void 0, hydrate, insert);
    }
  }
  function isComponent(name) {
    return name in componentsMeta_default.list;
  }

  // viewi/index.ts
  var Viewi = () => ({
    version: "2.0.1"
  });
  globalThis.Viewi = Viewi;
  console.log("Viewi entry");
  var counterTarget = document.getElementById("counter");
  function renderApp(name) {
    renderComponent(counterTarget, name, void 0, {}, true, false);
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
    componentsMeta_default.list = await (await fetch("/assets/components.json")).json();
    setTimeout(() => renderApp("TestComponent"), 500);
  })();
})();
