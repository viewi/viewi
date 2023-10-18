import { ContextScope } from "./contextScope";

export function dispose(scope: ContextScope) {
    // dispose reactivity tracker for removed html/template nodes
    if (scope.keep) return;
    for (let reactivityIndex in scope.track) {
        const reactivityItem = scope.track[reactivityIndex];
        delete scope.instance.$$r[reactivityItem.path][reactivityItem.id];
    }
    scope.track = [];
    if (scope.children) {
        for (let i in scope.children) {
            dispose(scope.children[i]);
        }
        scope.children = {};
    }
    if (scope.main) {
        // dispose instance
        for (let i = 0; i < scope.instance.$$p.length; i++) {
            const trackGroup = scope.instance.$$p[i];
            delete trackGroup[1].$$r[trackGroup[0]];
        }
        // TODO: call dispose hook
        const instance = scope.instance as any;
        if (instance.destroy) {
            instance.destroy();
        }
    }
    if (scope.parent) {
        delete scope.parent.children[scope.id];
        delete scope.parent;
    }
}