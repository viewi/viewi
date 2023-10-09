import { NodeType } from "./nodeType";
import { TemplateNode } from "./templateNode";

export function unpack(item: TemplateNode) {
    let nodeType: NodeType = 'value';
    switch (item.t) {
        case 't': {
            nodeType = 'tag';
            break;
        }
        case 'a': {
            nodeType = 'attr';
            break;
        }
        case undefined:
        case 'v': {
            nodeType = 'value';
            break;
        }
        case 'c': {
            nodeType = 'component';
            break;
        }
        case 'x': {
            nodeType = 'text';
            break;
        }
        case 'm': {
            nodeType = 'comment';
            break;
        }
        case 'r': {
            nodeType = 'root';
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
    };
}