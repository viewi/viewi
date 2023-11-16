const delayedQueue = {};

export const delay = {
    postpone: function (name: string, callback: Function) {
        delayedQueue[name] = callback;
    },
    ready: function (name: string) {
        if (!(name in delayedQueue)) {
            throw new Error("There is no postponed action for " + name);
        }
        delayedQueue[name]();
        delete delayedQueue[name];
    },
};