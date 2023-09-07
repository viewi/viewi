import { BaseComponent } from "./BaseComponent";
import { TextAnchor, createAnchorNode, getAnchor } from "./anchor";
import { ConditionalDirective, Directive, DirectiveMap, DirectiveStorageType, DirectiveType } from "./directive";
import { hydrateComment } from "./hydrateComment";
import { hydrateTag } from "./hydrateTag";
import { hydrateText } from "./hydrateText";
import { NodeType, TemplateNode } from "./node";
import { renderAttributeValue } from "./renderAttributeValue";
import { renderIf } from "./renderIf";
import { renderText } from "./renderText";
import { updateComment } from "./updateComment";

export function render(
    target: Node,
    instance: BaseComponent<any>,
    nodes: TemplateNode[],
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
        switch (node.type) {
            case <NodeType>'tag':
                {
                    // if, else-if, else, foreach
                    if (node.directives) {
                        const localDirectiveMap: DirectiveMap = directives || { map: {}, storage: {} };

                        for (let d = 0; d < node.directives.length; d++) {
                            const directive = node.directives[d];
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
                                    ](instance));
                                    ifConditions.values.push(nextValue);
                                    const anchor = getAnchor(target);
                                    createAnchorNode(anchor, target); // begin if
                                    if (nextValue) {
                                        render(target, instance, [node], localDirectiveMap);
                                    }
                                    const anchorNode = createAnchorNode(anchor, target); // end if
                                    if (directive.children![0].subs) {
                                        for (let subI in directive.children![0].subs) {
                                            const trackingPath = directive.children![0].subs[subI];
                                            ifConditions.subs.push(trackingPath);
                                            if (!instance.$$r[trackingPath]) {
                                                instance.$$r[trackingPath] = [];
                                            }
                                            instance.$$r[trackingPath].push([renderIf, [instance, node, directive, anchorNode, ifConditions, { ...localDirectiveMap }, ifConditions.index]]);
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
                                            ](instance));
                                        ifConditions.values.push(nextValue);
                                        const anchor = getAnchor(target);
                                        createAnchorNode(anchor, target); // begin else-if
                                        if (nextValue) {
                                            render(target, instance, [node], localDirectiveMap);
                                        }
                                        const anchorNode = createAnchorNode(anchor, target); // end else-if
                                        if (directive.children![0].subs) {
                                            // TODO: filter out unique
                                            ifConditions.subs = ifConditions.subs.concat(directive.children![0].subs);
                                        }
                                        for (let subI in ifConditions.subs) {
                                            const trackingPath = ifConditions.subs[subI];
                                            if (!instance.$$r[trackingPath]) {
                                                instance.$$r[trackingPath] = [];
                                            }
                                            instance.$$r[trackingPath].push([renderIf, [instance, node, directive, anchorNode, ifConditions, { ...localDirectiveMap }, ifConditions.index]]);
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
                                        const anchor = getAnchor(target);
                                        createAnchorNode(anchor, target); // begin else
                                        if (nextValue) {
                                            render(target, instance, [node], localDirectiveMap);
                                        }
                                        const anchorNode = createAnchorNode(anchor, target); // end else
                                        for (let subI in ifConditions.subs) {
                                            const trackingPath = ifConditions.subs[subI];
                                            if (!instance.$$r[trackingPath]) {
                                                instance.$$r[trackingPath] = [];
                                            }
                                            instance.$$r[trackingPath].push([renderIf, [instance, node, directive, anchorNode, ifConditions, { ...localDirectiveMap }, ifConditions.index]]);
                                        }

                                        ifConditions.index++;
                                        // continue to the next node
                                        breakAndContinue = true;
                                    } else {
                                        console.warn('Directive else has missing previous if/else-if', directive.content, directive);
                                    }
                                    break;
                                }
                                default: {
                                    console.warn('Directive not implemented', directive.content, directive);
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
                        textNode = hydrateText(target, instance, node);
                    } else {
                        textNode = document.createTextNode('');
                        renderText(instance, node, textNode);
                        insert
                            ? target.parentElement!.insertBefore(textNode, target)
                            : target.appendChild(textNode);
                    }
                    if (node.subs) {
                        for (let subI in node.subs) {
                            const trackingPath = node.subs[subI];
                            if (!instance.$$r[trackingPath]) {
                                instance.$$r[trackingPath] = [];
                            }
                            instance.$$r[trackingPath].push([renderText, [instance, node, textNode]]);
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
                        if (!instance.$$r[trackingPath]) {
                            instance.$$r[trackingPath] = [];
                        }
                        instance.$$r[trackingPath].push([updateComment, [instance, node, commentNode]]);
                    }
                }
                break;
            }
            default: {
                console.log('Not implemented', node);
                break;
            }
        }
        if (node.attributes) {
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
                        console.log('Event', attribute, eventName, eventHandler);
                    }
                } else {
                    renderAttributeValue(instance, attribute, <HTMLElement>element, attrName);
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
                            if (!instance.$$r[trackingPath]) {
                                instance.$$r[trackingPath] = [];
                            }
                            instance.$$r[trackingPath].push([renderAttributeValue, [instance, attribute, element, attrName]]);
                        }
                    }
                }
            }
        }
        if (node.children) {
            render(element, instance, node.children, undefined, hydrate, nextInsert);
        }
    }
}