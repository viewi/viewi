import { handleUrl } from "./handleUrl";
import { locationScope } from "./locationScope";

export function watchLinks() {
    locationScope.scrollTo = location.hash;
    document.addEventListener('click', function (event: MouseEvent) {
        if (event.defaultPrevented) {
            return;
        }
        if (!event.target) {
            console.warn('Can not acquire event target at "watchLinks".');
        }
        const target = <HTMLAnchorElement>event.target!;
        let nextTarget: HTMLAnchorElement = target;
        while (nextTarget.parentElement && nextTarget.tagName !== 'A') {
            nextTarget = <HTMLAnchorElement>nextTarget.parentElement;
        }
        if (
            nextTarget.tagName === 'A'
            && nextTarget.href
            && nextTarget.href.indexOf(location.origin) === 0
            && (nextTarget.target === "_self" || !nextTarget.target)
        ) {
            locationScope.scrollTo = null;
            locationScope.skipRender = false;
            if (nextTarget.hash && nextTarget.pathname === location.pathname) {
                locationScope.scrollTo = nextTarget.hash;
                locationScope.skipRender = true;
            } else {
                event.preventDefault(); // Cancel native event
                // e.stopPropagation(); // Don't bubble/capture the event      
                locationScope.scrollTo = nextTarget.hash;
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