import { BaseComponent } from "../component/baseComponent";
import { ContextScope } from "../lifecycle/contextScope";

let trackingId = 0;
export function nextTrackingId() {
    return ++trackingId;
}

export function track(instance: BaseComponent<any>, trackingPath: string, scope: ContextScope, action: [Function, any[]]) {
    if (!instance.$$r[trackingPath]) {
        instance.$$r[trackingPath] = {};
    }
    const trackId = ++trackingId;
    scope.track.push({ id: trackId, path: trackingPath });
    instance.$$r[trackingPath][trackId] = action;
}