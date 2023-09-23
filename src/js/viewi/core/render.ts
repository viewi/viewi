import { BaseComponent } from "./BaseComponent";
import { TextAnchor, createAnchorNode, getAnchor, nextAnchorNodeId } from "./anchor";
import { ArrayScope, ForeachAnchorEnum } from "./arrayScope";
import { ConditionalDirective, Directive, DirectiveMap, DirectiveStorageType, DirectiveType } from "./directive";
import { hydrateComment } from "./hydrateComment";
import { hydrateTag } from "./hydrateTag";
import { hydrateText } from "./hydrateText";
import { NodeType, TemplateNode } from "./node";
import { renderAttributeValue } from "./renderAttributeValue";
import { renderForeach } from "./renderForeach";
import { renderIf } from "./renderIf";
import { renderText } from "./renderText";
import { ContextScope } from "./contextScope";
import { updateComment } from "./updateComment";
import { track } from "./track";
import { renderComponent } from "./renderComponent";

export function render(
    target: Node,
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
        let element: Node = target;
        // let hydrate = true;
        let breakAndContinue = false;
        let withAttributes = false;
        switch (node.type) {
            case <NodeType>'tag':
                {
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
                                        arguments: scope.arguments,
                                        components: [],
                                        map: scope.map,
                                        track: [],
                                        parent: scope,
                                        children: {},
                                        counter: 0
                                    };
                                    scope.children[scopeId] = nextScope;
                                    if (nextValue) {
                                        render(target, instance, [node], nextScope, localDirectiveMap, hydrate, insert);
                                    }
                                    const anchorNode = createAnchorNode(target, insert, anchor, anchorBegin._anchor); // end if
                                    if (directive.children![0].subs) {
                                        for (let subI in directive.children![0].subs) {
                                            const trackingPath = directive.children![0].subs[subI];
                                            ifConditions.subs.push(trackingPath);
                                            track(instance, trackingPath, scope, [renderIf, [instance, node, nextScope, directive, anchorNode, ifConditions, nextDirectives, ifConditions.index]]);
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
                                            arguments: scope.arguments,
                                            components: [],
                                            map: scope.map,
                                            track: [],
                                            parent: scope,
                                            children: {},
                                            counter: 0
                                        };
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
                                            track(instance, trackingPath, scope, [renderIf, [instance, node, nextScope, directive, anchorNode, ifConditions, nextDirectives, ifConditions.index]]);
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
                                            arguments: scope.arguments,
                                            components: [],
                                            map: scope.map,
                                            track: [],
                                            parent: scope,
                                            children: {},
                                            counter: 0
                                        };
                                        scope.children[scopeId] = nextScope;
                                        if (nextValue) {
                                            render(target, instance, [node], nextScope, localDirectiveMap, hydrate, insert);
                                        }
                                        const anchorNode = createAnchorNode(target, insert, anchor, anchorBegin._anchor); // end else
                                        for (let subI in ifConditions.subs) {
                                            const trackingPath = ifConditions.subs[subI];
                                            track(instance, trackingPath, scope, [renderIf, [instance, node, nextScope, directive, anchorNode, ifConditions, nextDirectives, ifConditions.index]]);
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
                                    const dataArrayScope: ArrayScope = {};
                                    for (let forKey in data) {
                                        const dataKey = isNumeric ? +forKey : forKey;
                                        const dataItem = data[dataKey];
                                        const scopeId = ++scope.counter;
                                        const nextScope: ContextScope = {
                                            id: scopeId,
                                            arguments: [...scope.arguments],
                                            components: [],
                                            map: { ...scope.map },
                                            track: [],
                                            parent: scope,
                                            children: {},
                                            counter: 0
                                        };
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
                                        dataArrayScope[dataKey] = {
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
                                                [instance, node, directive, anchorNode, dataArrayScope, nextDirectives, scope]]);
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
                    // template
                    if (node.content === 'template') {
                        nextInsert = insert;
                        break;
                    }
                    withAttributes = true;
                    const content = node.expression
                        ? instance.$$t[node.code!](instance)
                        : (node.content ?? '');
                    element = hydrate
                        ? hydrateTag(target, content)
                        : (insert
                            ? target.parentElement!.insertBefore(document.createElement(content), target)
                            : target.appendChild(document.createElement(content)));
                    // TODO: reactive tag/component
                    break;
                }
            case <NodeType>'text':
                {
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
            case <NodeType>'component': {
                renderComponent(target, node.content!);
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
                for (let a in node.attributes) {
                    const attribute = node.attributes[a];
                    const attrName = attribute.expression
                        ? instance.$$t[attribute.code!](instance)
                        : (attribute.content ?? '');
                    if (attrName[0] === '(') {
                        // event
                        const eventName = attrName.substring(1, attrName.length - 1);
                        if (attribute.children) {
                            const eventHandler =
                                instance.$$t[
                                    attribute.dynamic
                                        ? attribute.dynamic.code!
                                        : attribute.children[0].code!
                                ](instance) as EventListener;
                            element.addEventListener(eventName, eventHandler);
                            // console.log('Event', attribute, eventName, eventHandler);
                        }
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
                        if (valueSubs) {
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
            render(element, instance, node.children, scope, undefined, hydrate, nextInsert);
        }
    }
}