import { BaseComponent } from "./BaseComponent";
import { ContextScope } from "./contextScope";

export function dispose(scope: ContextScope, instance: BaseComponent<any>) {
    // dispose reactivity tracker for removed html/template nodes
    for (let reactivityIndex in scope.track) {
        const reactivityItem = scope.track[reactivityIndex];
        delete instance.$$r[reactivityItem.path][reactivityItem.id];
    }
    scope.track = [];
    // TODO: dispose scope components
    scope.components = [];
    if (scope.children) {
        for (let i in scope.children) {
            dispose(scope.children[i], instance);
        }
        scope.children = {};
    }
    if (scope.parent) {
        delete scope.parent.children[scope.id];
    }
}