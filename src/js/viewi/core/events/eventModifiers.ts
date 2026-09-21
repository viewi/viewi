/**
 * Event modifiers: `(keyup.enter)`, `(keydown.escape)`, `(click.prevent)`, `(keydown.ctrl.enter)`.
 *
 * Before this, the attribute text went to addEventListener as it was written, so `(keyup.enter)`
 * listened for an event literally named "keyup.enter" - one no browser ever fires - and the
 * handler silently never ran. The template compiler now rejects an unknown modifier at build time
 * (Viewi\TemplateCompiler\EventModifiers keeps the same list), and this turns the known ones into
 * a guard around the handler.
 *
 * Keys are matched on `event.key`, which names the key the person meant ('Enter', 'Escape', ' ')
 * rather than the deprecated numeric keyCode. Listing two keys means either: `(keydown.up.down)`
 * runs for both arrows.
 */

/** Modifier => the `event.key` values it matches. */
const keyModifiers: { [modifier: string]: string[] } = {
    enter: ['Enter'],
    esc: ['Escape', 'Esc'],
    escape: ['Escape', 'Esc'],
    space: [' ', 'Spacebar'],
    tab: ['Tab'],
    up: ['ArrowUp', 'Up'],
    down: ['ArrowDown', 'Down'],
    left: ['ArrowLeft', 'Left'],
    right: ['ArrowRight', 'Right'],
    arrowup: ['ArrowUp', 'Up'],
    arrowdown: ['ArrowDown', 'Down'],
    arrowleft: ['ArrowLeft', 'Left'],
    arrowright: ['ArrowRight', 'Right'],
    home: ['Home'],
    end: ['End'],
    pageup: ['PageUp'],
    pagedown: ['PageDown'],
    // Both, as elsewhere: people say "delete" for the key that removes the thing before the cursor.
    delete: ['Delete', 'Backspace'],
    backspace: ['Backspace'],
};

/** Held-key modifiers: the handler runs only while that key is down. */
const systemModifiers: { [modifier: string]: 'ctrlKey' | 'shiftKey' | 'altKey' | 'metaKey' } = {
    ctrl: 'ctrlKey',
    shift: 'shiftKey',
    alt: 'altKey',
    meta: 'metaKey',
};

/** Behaviour modifiers applied to the event itself. */
const behaviourModifiers = ['prevent', 'stop', 'self', 'exact'];

/** Listener options, passed to addEventListener rather than checked per event. */
const optionModifiers = ['once', 'passive', 'capture'];

export type ParsedEvent = {
    name: string,
    wrap: (handler: EventListener) => EventListener,
    options?: AddEventListenerOptions
};

/** `keyup.enter.prevent` -> the real event name, a handler wrapper, and listener options. */
export function parseEventName(attributeName: string): ParsedEvent {
    const parts = attributeName.split('.');
    const name = parts[0];
    if (parts.length === 1) {
        return { name, wrap: (handler) => handler };
    }
    const keys: string[] = [];
    const system: ('ctrlKey' | 'shiftKey' | 'altKey' | 'metaKey')[] = [];
    let prevent = false;
    let stop = false;
    let self = false;
    let exact = false;
    let options: AddEventListenerOptions | undefined = undefined;
    for (let i = 1; i < parts.length; i++) {
        const modifier = parts[i].toLowerCase();
        if (modifier in keyModifiers) {
            keys.push(...keyModifiers[modifier]);
        } else if (modifier in systemModifiers) {
            system.push(systemModifiers[modifier]);
        } else if (modifier === 'prevent') {
            prevent = true;
        } else if (modifier === 'stop') {
            stop = true;
        } else if (modifier === 'self') {
            self = true;
        } else if (modifier === 'exact') {
            exact = true;
        } else if (optionModifiers.indexOf(modifier) !== -1) {
            options = options ?? {};
            (<any>options)[modifier] = true;
        } else {
            // The build refuses these; a template compiled by an older build still must not throw.
            console.warn(`Viewi: unknown event modifier "${modifier}" in (${attributeName}), ignored.`);
        }
    }
    const wrap = function (handler: EventListener): EventListener {
        return function (event: Event) {
            if (self && event.target !== event.currentTarget) {
                return;
            }
            if (keys.length > 0 && keys.indexOf((<KeyboardEvent>event).key) === -1) {
                return;
            }
            for (let s = 0; s < system.length; s++) {
                if (!(<any>event)[system[s]]) {
                    return;
                }
            }
            if (exact) {
                // Only the listed held keys: (keydown.ctrl.enter.exact) ignores Ctrl+Shift+Enter.
                for (const modifier in systemModifiers) {
                    const flag = systemModifiers[modifier];
                    if ((<any>event)[flag] && system.indexOf(flag) === -1) {
                        return;
                    }
                }
            }
            if (prevent) {
                event.preventDefault();
            }
            if (stop) {
                event.stopPropagation();
            }
            return handler(event);
        };
    };
    return { name, wrap, options };
}

/** For the compiler's list and the docs: every modifier this runtime understands. */
export const knownEventModifiers = [
    ...Object.keys(keyModifiers),
    ...Object.keys(systemModifiers),
    ...behaviourModifiers,
    ...optionModifiers,
];
