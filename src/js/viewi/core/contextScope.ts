import { BaseComponent } from "./BaseComponent";

export type ContextScope = {
    arguments: any[],
    map: { [key: string]: number },
    components: BaseComponent<any>[],
    track: number[]
}

let trackingId = 0;
export function nextTrackingId() {
    return ++trackingId;
}

export function track(instance: BaseComponent<any>, trackingPath: string, action: [Function, any[]]) {
    if (!instance.$$r[trackingPath]) {
        instance.$$r[trackingPath] = {};
    }
    instance.$$r[trackingPath][++trackingId] = action;
}