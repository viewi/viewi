<?php

namespace Viewi\TemplateCompiler;

use Exception;

/**
 * The event modifiers a template may use: `(keyup.enter)`, `(keydown.ctrl.enter)`, `(click.prevent)`.
 *
 * The runtime applies them (src/js/viewi/core/events/eventModifiers.ts); this list only lets the
 * build refuse one it does not know. Without that, a typo - or a modifier from another framework -
 * compiled into a listener for an event named "keyup.entr" that no browser fires, and the handler
 * silently never ran. Keep the two lists in step.
 */
class EventModifiers
{
    public const KEYS = [
        'enter', 'esc', 'escape', 'space', 'tab',
        'up', 'down', 'left', 'right', 'arrowup', 'arrowdown', 'arrowleft', 'arrowright',
        'home', 'end', 'pageup', 'pagedown', 'delete', 'backspace',
    ];
    public const SYSTEM = ['ctrl', 'shift', 'alt', 'meta'];
    public const BEHAVIOUR = ['prevent', 'stop', 'self', 'exact'];
    public const OPTIONS = ['once', 'passive', 'capture'];

    /**
     * Throw when an event attribute such as "(keyup.entr)" carries a modifier the runtime does not
     * understand. The compiler attaches the attribute's file position to the exception.
     */
    public static function validate(string $attribute): void
    {
        $name = trim($attribute, '()');
        $parts = explode('.', $name);
        if (count($parts) < 2) {
            return;
        }
        $known = array_merge(self::KEYS, self::SYSTEM, self::BEHAVIOUR, self::OPTIONS);
        foreach (array_slice($parts, 1) as $modifier) {
            if (!in_array(strtolower($modifier), $known, true)) {
                throw new Exception(
                    "Unknown event modifier \"$modifier\" in $attribute. Known modifiers: "
                        . implode(', ', $known) . '.'
                );
            }
        }
    }
}
