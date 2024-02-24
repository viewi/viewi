import { handleUrl } from "./handleUrl";
import { locationScope } from "./locationScope";

export function watchLinks() {
    document.addEventListener('click', function (event: MouseEvent) {
        if (event.defaultPrevented) {
            return;
        }
        if (!event.target) {
            console.warn('Can not aquire event target at "watchLinks".');
        }
        const target = <HTMLLinkElement>event.target!;
        let nextTarget: HTMLLinkElement = target;
        while (nextTarget.parentElement && nextTarget.tagName !== 'A') {
            nextTarget = <HTMLLinkElement>nextTarget.parentElement;
        }
        if (
            nextTarget.tagName === 'A' 
            && nextTarget.href 
            && nextTarget.href.indexOf(location.origin) === 0
            && (nextTarget.target === "_self" || !nextTarget.target)
        ) {
            locationScope.scrollTo = null;
            if (
                !locationScope.link.hash
                || locationScope.link.pathname !== location.pathname
            ) {
                event.preventDefault(); // Cancel native event
                // e.stopPropagation(); // Don't bubble/capture the event
                if (locationScope.link.hash) {
                    locationScope.scrollTo = locationScope.link.hash;
                }
                handleUrl(nextTarget.href, true);
            }
        }
    }, false);

    // handle back button
    window.addEventListener('popstate', function (event) {
        if (event.state)
            handleUrl(event.state.href, false);
        else
            handleUrl(location.href, false);
    });
}