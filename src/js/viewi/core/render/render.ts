import { BaseComponent } from "../component/baseComponent";
import { hydrateComment } from "../hydrate/hydrateComment";
import { hydrateTag } from "../hydrate/hydrateTag";
import { hydrateText } from "../hydrate/hydrateText";
import { TemplateNode } from "../node/templateNode";
import { renderAttributeValue } from "./renderAttributeValue";
import { renderForeach } from "./renderForeach";
import { renderIf } from "./renderIf";
import { renderText } from "./renderText";
import { updateComment } from "../reactivity/handlers/updateComment";
import { track } from "../reactivity/track";
import { renderComponent } from "./renderComponent";
import { unpack } from "../node/unpack";
import { renderDynamic } from "./renderDynamic";
import { PropsContext } from "../lifecycle/propsContext";
import { Slots } from "../node/slots";
import { renderRaw } from "./renderRaw";
import { getModelHandler } from "../reactivity/handlers/getModelHandler";
import { HTMLModelInputElement, updateModelValue } from "../reactivity/handlers/updateModelValue";
import { InputType } from "../node/inputType";
import { ModelHandler } from "../reactivity/handlers/modelHandler";
import { isComponent } from "../component/isComponent";
import { DirectiveMap } from "../directive/directiveMap";
import { DirectiveType } from "../directive/directiveType";
import { ConditionalDirective } from "../directive/conditionalDirective";
import { Anchor } from "../anchor/anchor";
import { createAnchorNode, nextAnchorNodeId } from "../anchor/createAnchorNode";
import { getAnchor } from "../anchor/getAnchor";
import { TextAnchor } from "../anchor/textAnchor";
import { ForeachAnchorEnum } from "../anchor/foreachAnchorEnum";
import { ContextScope } from "../lifecycle/contextScope";
import { NodeType } from "../node/nodeType";
import { ArrayScope } from "../lifecycle/arrayScope";
import { globalScope } from "../di/globalScope";
import { isSvg } from "../helpers/isSvg";
import { svgNameSpace } from "../helpers/svgNameSpace";
import { HtmlNodeType } from "../node/htmlNodeType";
import { hydrateRaw } from "../hydrate/hydrateRaw";

export function render(
    target: HtmlNodeType,
    instance: BaseComponent<any>,
    nodes: TemplateNode[],
    scope: ContextScope,
    directives?: DirectiveMap,
    hydrate: boolean = true,
    insert: boolean = false
) {
    let ifConditions: ConditionalDirective | null = null;
    let nextInsert = false;
    for (let i = 0; i < nodes.length; i++) {
        const node = nodes[i];
        let element: HtmlNodeType = target;
        // let hydrate = true;
        let breakAndContinue = false;
        let withAttributes = false;
        let childScope = scope;
        switch (node.type) {
            case <NodeType>'tag':
            case <NodeType>'component': {
                // if, else-if, else, foreach
                if (node.directives) {
                    const localDirectiveMap: DirectiveMap = directives || { map: {}, storage: {} };
                    let callArguments = [instance];
                    if (scope.arguments) {
                        callArguments = callArguments.concat(scope.arguments);
                    }
                    for (let d = 0; d < node.directives.length; d++) {
                        const directive: TemplateNode = node.directives[d];
                        if (d in localDirectiveMap.map) { // already processed                                 
                            // console.log('skipping', localDirectiveMap, directive);
                            continue;
                        }
                        localDirectiveMap.map[d] = true;
                        switch (directive.content) {
                            case <DirectiveType>'if': {
                                // new conditions
                                ifConditions = <ConditionalDirective>{ values: [], index: 0, subs: [] };
                                const nextValue = !!(instance.$$t[
                                    directive.children![0].code!
                                ].apply(null, callArguments));
                                ifConditions.values.push(nextValue);
                                const anchor = hydrate ? getAnchor(target) : undefined;
                                const anchorBegin = createAnchorNode(target, insert, anchor); // begin if
                                const nextDirectives: DirectiveMap = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };
                                const scopeId = ++scope.counter;
                                const nextScope: ContextScope = {
                                    id: scopeId,
                                    why: 'if',
                                    arguments: scope.arguments,
                                    map: scope.map,
                                    instance: instance,
                                    lastComponent: scope.lastComponent,
                                    track: [],
                                    parent: scope,
                                    children: {},
                                    counter: 0,
                                    slots: scope.slots
                                };
                                if (scope.refs) {
                                    nextScope.refs = scope.refs;
                                }
                                scope.children[scopeId] = nextScope;
                                if (nextValue) {
                                    render(target, instance, [node], nextScope, localDirectiveMap, hydrate, insert);
                                }
                                const anchorNode = createAnchorNode(target, insert, anchor, anchorBegin._anchor); // end if
                                if (directive.children![0].subs) {
                                    for (let subI in directive.children![0].subs) {
                                        const trackingPath = directive.children![0].subs[subI];
                                        ifConditions.subs.push(trackingPath);
                                        track(instance, trackingPath, scope, [renderIf, [instance, node, { scope: nextScope, anchorNode }, directive, ifConditions, nextDirectives, ifConditions.index]]);
                                    }
                                }
                                ifConditions.index++;
                                // continue to the next node
                                breakAndContinue = true;
                                break;
                            }
                            case <DirectiveType>'else-if': {
                                // console.log('else if', ifConditions);
                                if (ifConditions) {
                                    let nextValue = true;
                                    for (let ifv = 0; ifv < ifConditions.index; ifv++) {
                                        nextValue = nextValue && !ifConditions.values[ifv];
                                    }
                                    nextValue = nextValue && !ifConditions.values[ifConditions.index - 1]
                                        && !!(instance.$$t[
                                            directive.children![0].code!
                                        ].apply(null, callArguments));
                                    ifConditions.values.push(nextValue);
                                    const anchor = hydrate ? getAnchor(target) : undefined;
                                    const anchorBegin = createAnchorNode(target, insert, anchor); // begin else-if
                                    const nextDirectives: DirectiveMap = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };
                                    const scopeId = ++scope.counter;
                                    const nextScope: ContextScope = {
                                        id: scopeId,
                                        why: 'elseif',
                                        instance: instance,
                                        lastComponent: scope.lastComponent,
                                        arguments: scope.arguments,
                                        map: scope.map,
                                        track: [],
                                        parent: scope,
                                        children: {},
                                        counter: 0,
                                        slots: scope.slots
                                    };
                                    if (scope.refs) {
                                        nextScope.refs = scope.refs;
                                    }
                                    scope.children[scopeId] = nextScope;
                                    if (nextValue) {
                                        render(target, instance, [node], nextScope, localDirectiveMap, hydrate, insert);
                                    }
                                    const anchorNode = createAnchorNode(target, insert, anchor, anchorBegin._anchor); // end else-if
                                    if (directive.children![0].subs) {
                                        // TODO: filter out unique
                                        ifConditions.subs = ifConditions.subs.concat(directive.children![0].subs);
                                    }
                                    for (let subI in ifConditions.subs) {
                                        const trackingPath = ifConditions.subs[subI];
                                        track(instance, trackingPath, scope, [renderIf, [instance, node, { scope: nextScope, anchorNode }, directive, ifConditions, nextDirectives, ifConditions.index]]);
                                    }

                                    ifConditions.index++;
                                    // continue to the next node
                                    breakAndContinue = true;
                                } else {
                                    console.warn('Directive else-if has missing previous if/else-if', directive.content, directive);
                                }
                                // console.log(ifConditions);
                                break;
                            }
                            case <DirectiveType>'else': {
                                if (ifConditions) {
                                    let nextValue = true;
                                    for (let ifv = 0; ifv < ifConditions.index; ifv++) {
                                        nextValue = nextValue && !ifConditions.values[ifv];
                                    }
                                    ifConditions.values.push(nextValue);
                                    const anchor = hydrate ? getAnchor(target) : undefined;
                                    const anchorBegin = createAnchorNode(target, insert, anchor); // begin else
                                    const nextDirectives: DirectiveMap = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };

                                    const scopeId = ++scope.counter;
                                    const nextScope: ContextScope = {
                                        id: scopeId,
                                        why: "else",
                                        instance: instance,
                                        lastComponent: scope.lastComponent,
                                        arguments: scope.arguments,
                                        map: scope.map,
                                        track: [],
                                        parent: scope,
                                        children: {},
                                        counter: 0,
                                        slots: scope.slots
                                    };
                                    if (scope.refs) {
                                        nextScope.refs = scope.refs;
                                    }
                                    scope.children[scopeId] = nextScope;
                                    if (nextValue) {
                                        render(target, instance, [node], nextScope, localDirectiveMap, hydrate, insert);
                                    }
                                    const anchorNode = createAnchorNode(target, insert, anchor, anchorBegin._anchor); // end else
                                    for (let subI in ifConditions.subs) {
                                        const trackingPath = ifConditions.subs[subI];
                                        track(instance, trackingPath, scope, [renderIf, [instance, node, { scope: nextScope, anchorNode }, directive, ifConditions, nextDirectives, ifConditions.index]]);
                                    }

                                    ifConditions.index++;
                                    // continue to the next node
                                    breakAndContinue = true;
                                } else {
                                    console.warn('Directive else has missing previous if/else-if', directive.content, directive);
                                }
                                break;
                            }
                            case <DirectiveType>'foreach': {
                                const data = instance.$$t[
                                    directive.children![0].forData!
                                ].apply(null, callArguments);
                                const anchor = hydrate ? getAnchor(target) : undefined;
                                const anchorBegin = createAnchorNode(target, insert, anchor); // begin foreach
                                const isNumeric = Array.isArray(data);
                                const dataArrayScope: ArrayScope = { data: {} };
                                for (let forKey in data) {
                                    const dataKey = isNumeric ? +forKey : forKey;
                                    const dataItem = data[dataKey];
                                    const scopeId = ++scope.counter;
                                    const nextScope: ContextScope = {
                                        id: scopeId,
                                        why: "foreach",
                                        instance: instance,
                                        lastComponent: scope.lastComponent,
                                        arguments: [...scope.arguments],
                                        map: { ...scope.map },
                                        track: [],
                                        parent: scope,
                                        children: {},
                                        counter: 0,
                                        slots: scope.slots
                                    };
                                    if (scope.refs) {
                                        nextScope.refs = scope.refs;
                                    }
                                    scope.children[scopeId] = nextScope;
                                    nextScope.map[directive.children![0].forKey!] = nextScope.arguments.length;
                                    nextScope.arguments.push(dataKey);
                                    nextScope.map[directive.children![0].forItem!] = nextScope.arguments.length;
                                    nextScope.arguments.push(dataItem);

                                    // console.log('foreach', data, dataKey, dataItem, nextScope);
                                    const nextDirectives: DirectiveMap = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };
                                    const itemBeginAnchor = createAnchorNode(target, insert, anchor, ForeachAnchorEnum.BeginAnchor + nextAnchorNodeId()); // begin foreach item
                                    render(target, instance, [node], nextScope, nextDirectives, hydrate, insert);
                                    const itemEndAnchor = createAnchorNode(target, insert, anchor, itemBeginAnchor._anchor); // end foreach item
                                    dataArrayScope.data[dataKey] = {
                                        key: dataKey,
                                        value: dataItem,
                                        begin: itemBeginAnchor,
                                        end: itemEndAnchor,
                                        scope: nextScope
                                    };
                                }
                                const anchorNode = createAnchorNode(target, insert, anchor, anchorBegin._anchor); // end foreach

                                if (directive.children![0].subs) {
                                    for (let subI in directive.children![0].subs) {
                                        const trackingPath = directive.children![0].subs[subI];
                                        const nextDirectives: DirectiveMap = { map: { ...localDirectiveMap.map }, storage: { ...localDirectiveMap.storage } };
                                        track(instance, trackingPath, scope, [renderForeach,
                                            [instance, node, directive, { anchorBegin, anchorNode }, dataArrayScope, nextDirectives, scope]]);
                                    }
                                }

                                breakAndContinue = true;
                                break;
                            }
                            default: {
                                console.warn('Directive not implemented', directive.content, directive);
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
                const content = node.expression
                    ? instance.$$t[node.code!](instance)
                    : (node.content ?? '');
                const isDynamic = node.expression;
                const componentTag = node.type === "component"
                    || (node.expression && isComponent(content));

                let anchor: Anchor | undefined;
                let anchorBegin: TextAnchor | undefined;
                let nextScope: ContextScope = scope;
                if (isDynamic) {
                    anchor = hydrate ? getAnchor(target) : undefined;
                    anchorBegin = createAnchorNode(target, insert, anchor); // begin dynamic
                }
                if (isDynamic) {
                    const scopeId = ++scope.counter;
                    nextScope = {
                        id: scopeId,
                        why: 'dynamic',
                        arguments: [...scope.arguments],
                        map: { ...scope.map },
                        track: [],
                        instance: instance,
                        lastComponent: scope.lastComponent,
                        parent: scope,
                        children: {},
                        counter: 0,
                        slots: scope.slots
                    };
                    if (scope.refs) {
                        nextScope.refs = scope.refs;
                    }
                    scope.children[scopeId] = nextScope;
                    childScope = nextScope;
                }
                // component
                if (componentTag) {
                    const slots: Slots = {};
                    if (node.slots) {
                        const scopeId = ++nextScope!.counter;
                        const slotScope: ContextScope = {
                            id: scopeId,
                            why: "slot",
                            arguments: [...scope.arguments],
                            map: { ...scope.map },
                            track: [],
                            parent: nextScope,
                            instance: instance,
                            lastComponent: { instance: scope.lastComponent.instance },
                            children: {},
                            counter: 0,
                            slots: scope.slots
                        };
                        if (scope.refs) {
                            slotScope.refs = scope.refs;
                        }
                        nextScope!.children[scopeId] = slotScope;
                        for (let slotName in node.slots) {
                            slots[slotName] = {
                                node: node.slots[slotName],
                                scope: slotScope
                            };
                        }
                    }
                    renderComponent(target, content, <PropsContext>{ attributes: node.attributes, scope: scope }, slots, hydrate, insert);
                } else {
                    // template
                    if (node.content === 'template') {
                        nextInsert = insert;
                        break;
                    }
                    if (node.content === 'slot') {
                        if (!anchor) {
                            anchor = hydrate ? getAnchor(target) : undefined;
                        }
                        nextInsert = insert;
                        let slotName: string = 'default';
                        if (node.attributes) {
                            for (let attrIndex in node.attributes) {
                                if (node.attributes[attrIndex].content === 'name') {
                                    slotName = node.attributes![attrIndex]!.children![0]!.content!;
                                }
                            }
                        }
                        const anchorSlotBegin = createAnchorNode(target, insert, anchor); // begin slot
                        if (slotName in scope.slots!) { // slot from parent
                            const slot = scope.slots![slotName];
                            if (!slot.node.unpacked) {
                                unpack(slot.node);
                                slot.node.unpacked = true;
                            }
                            slot.scope.lastComponent.instance = scope.lastComponent.instance;
                            render(element, slot.scope.instance, slot.node.children!, slot.scope, undefined, hydrate, nextInsert);
                        } else { // default slot content
                            if (node.children) {
                                render(element, instance, node.children, scope, undefined, hydrate, nextInsert);
                            }
                        }
                        const anchorSlotNode = createAnchorNode(target, insert, anchor, anchorSlotBegin!._anchor); // end slot
                        if (scope.instance._name in globalScope.iteration) {
                            globalScope.iteration[scope.instance._name].slots[slotName] = anchorSlotNode;
                        }
                        continue;
                    }
                    withAttributes = true;
                    const isSvgNode = isSvg(content) || target.isSvg;
                    element = hydrate
                        ? hydrateTag(target, content)
                        : (insert
                            ? target.parentElement!.insertBefore(isSvgNode ? document.createElementNS(svgNameSpace, content) : document.createElement(content), target)
                            : target.appendChild(isSvgNode ? document.createElementNS(svgNameSpace, content) : document.createElement(content)));
                    if (node.first) {
                        instance._element = element;
                    }
                    if (isSvgNode) {
                        element.isSvg = true;
                    }
                }
                if (isDynamic) {
                    const anchorNode = createAnchorNode(target, insert, anchor, anchorBegin!._anchor); // end dynamic
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
            case <NodeType>'text':
                {
                    if (node.raw) {
                        const parentTagNode = insert ? target.parentElement! : target;
                        const vdom = document.createElement(parentTagNode.nodeName);
                        let callArguments = [instance];
                        if (scope.arguments) {
                            callArguments = callArguments.concat(scope.arguments);
                        }
                        const content = (node.expression
                            ? instance.$$t[node.code as number].apply(null, callArguments)
                            : node.content) ?? '';
                        vdom.innerHTML = content;
                        const anchor: Anchor | undefined = hydrate ? getAnchor(target) : undefined;
                        const anchorBegin = createAnchorNode(target, insert, anchor); // begin raw
                        if (hydrate) {
                            anchor!.current++;
                            hydrateRaw(vdom, anchor!, target);
                        } else {
                            if (vdom.childNodes.length > 0) {
                                const rawNodes = Array.prototype.slice.call(vdom.childNodes);
                                for (let rawNodeI = 0; rawNodeI < rawNodes.length; rawNodeI++) {
                                    const rawNode = rawNodes[rawNodeI];
                                    insert
                                        ? target.parentElement!.insertBefore(rawNode, target)
                                        : target.appendChild(rawNode);
                                }
                            }
                        }
                        const anchorNode = createAnchorNode(target, insert, anchor, anchorBegin!._anchor); // end raw
                        if (node.subs) {
                            for (let subI in node.subs) {
                                const trackingPath = node.subs[subI];
                                track(instance, trackingPath, scope, [renderRaw, [instance, node, scope, anchorNode]]);
                            }
                        }
                        break;
                    }
                    let textNode: Text;
                    if (hydrate) {
                        textNode = hydrateText(target, instance, node, scope);
                    } else {
                        textNode = document.createTextNode('');
                        renderText(instance, node, textNode, scope);
                        insert
                            ? target.parentElement!.insertBefore(textNode, target)
                            : target.appendChild(textNode);
                    }
                    if (node.subs) {
                        for (let subI in node.subs) {
                            const trackingPath = node.subs[subI];
                            track(instance, trackingPath, scope, [renderText, [instance, node, textNode, scope]]);
                        }
                    }
                    break;
                }
            case <NodeType>'comment': {
                const content = node.expression
                    ? instance.$$t[node.code!](instance)
                    : (node.content ?? '');
                const commentNode = hydrate
                    ? hydrateComment(target, content)
                    : (insert
                        ? target.parentElement!.insertBefore(document.createComment(content), target)
                        : target.appendChild(document.createComment(content)));
                if (node.subs) {
                    for (let subI in node.subs) {
                        const trackingPath = node.subs[subI];
                        track(instance, trackingPath, scope, [updateComment, [instance, node, commentNode]]);
                    }
                }
                break;
            }
            case <NodeType>'doctype': {
                if (hydrate) {
                    const anchor: Anchor = getAnchor(target);
                    anchor.current++;
                }
                break;
            }
            default: {
                console.warn('Node type not implemented', node);
                break;
            }
        }
        if (withAttributes) {
            if (node.attributes) {
                const toRemove: string[] | null = hydrate
                    ? (<HTMLElement>element).getAttributeNames()
                    : null;
                const hasMap: { [key: string]: boolean } | null = hydrate ? {} : null;
                for (let a = 0; a < node.attributes.length; a++) {
                    let callArguments = [instance];
                    if (scope.arguments) {
                      callArguments = callArguments.concat(scope.arguments);
                    }
                    const attribute: TemplateNode = node.attributes[a];
                    const attrName = attribute.expression
                        ? instance.$$t[attribute.code!].apply(null, callArguments)
                        : (attribute.content ?? '');
                    if (attrName[0] === '#') {
                        const refName = attrName.substring(1, attrName.length);
                        instance._refs[refName] = element;
                        if (scope.refs && refName in scope.refs) {
                            instance[refName] = element;
                        }
                        continue;
                    }
                    const isModel = attrName === 'model';
                    if (attrName[0] === '(') {
                        // event
                        const eventName = attrName.substring(1, attrName.length - 1);
                        if (attribute.children) {
                            const eventHandler =
                                instance.$$t[
                                    attribute.dynamic
                                        ? attribute.dynamic.code!
                                        : attribute.children[0].code!
                                ].apply(null, callArguments) as EventListener;
                            element.addEventListener(eventName, eventHandler);
                            // console.log('Event', attribute, eventName, eventHandler);
                        }
                    } else if (isModel) {
                        let inputType: InputType = "text";
                        (<HTMLElement>element).getAttribute('type') === 'checkbox' && (inputType = "checkbox");
                        (<HTMLElement>element).getAttribute('type') === 'radio' && (inputType = "radio");
                        let isMultiple = false;
                        if ((<HTMLElement>element).tagName === 'SELECT') {
                            inputType = "select";
                            isMultiple = (<HTMLSelectElement>element).multiple;
                        }
                        const isOnChange = inputType === "checkbox"
                            || inputType === "radio" || inputType === "select";
                        const valueNode = attribute.children![0];
                        const getterSetter: [(_component: BaseComponent<any>) => any, (_component: BaseComponent<any>, value: any) => void] = instance.$$t[valueNode.code!].apply(null, callArguments);
                        const eventName = isOnChange ? 'change' : 'input';
                        const inputOptions: ModelHandler = {
                            getter: getterSetter[0],
                            setter: getterSetter[1],
                            inputType: inputType,
                            isMultiple: isMultiple
                        };
                        // set initial value
                        // wait for child options to be rendered
                        setTimeout(() => updateModelValue(<HTMLModelInputElement>element, instance, inputOptions), 0);
                        // watch for property changes
                        for (let subI in valueNode.subs!) {
                            const trackingPath = valueNode.subs[subI];
                            track(instance, trackingPath, scope, [updateModelValue, [element, instance, inputOptions]]);
                        }
                        // handle input change
                        (<HTMLElement>element).addEventListener(eventName, getModelHandler(
                            instance,
                            inputOptions
                        ));
                    } else {
                        hydrate && (hasMap![attrName] = true);
                        renderAttributeValue(instance, attribute, <HTMLElement>element, attrName, scope);
                        let valueSubs = []; // TODO: on backend, pass attribute value subs in attribute
                        if (attribute.children) {
                            for (let av in attribute.children) {
                                const attributeValue = attribute.children[av];
                                if (attributeValue.subs) {
                                    valueSubs = valueSubs.concat(attributeValue.subs as never[]);
                                }
                            }
                        }
                        if (valueSubs.length) {
                            for (let subI in valueSubs) {
                                const trackingPath = valueSubs[subI];
                                track(instance, trackingPath, scope, [renderAttributeValue, [instance, attribute, element, attrName, scope]]);
                            }
                        }
                    }
                }
                if (hydrate) {
                    for (let ai = 0; ai < toRemove!.length; ai++) {
                        if (!(toRemove![ai] in hasMap!)) {
                            (<HTMLElement>element).removeAttribute(toRemove![ai]);
                        }
                    }
                }
            } else if (hydrate) {
                const toRemove: string[] = (<HTMLElement>element).getAttributeNames();
                for (let ai = 0; ai < toRemove.length; ai++) {
                    (<HTMLElement>element).removeAttribute(toRemove[ai]);
                }
            }
        }
        if (node.children) {
            render(element, instance, node.children, childScope, undefined, hydrate, nextInsert);
        }
    }
}