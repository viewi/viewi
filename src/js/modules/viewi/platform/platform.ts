import { componentsMeta } from "../../../viewi/core/component/componentsMeta";
import { factory } from "../../../viewi/core/di/factory";
import { handleUrl, onUrlUpdate } from "../../../viewi/core/router/handleUrl";

class Platform {
    browser: true = true;
    server: false = false;

    constructor() {
    }

    getConfig() {
        return componentsMeta.config;
    }

    redirect(url: string) {
        handleUrl(url, true, true);
    }

    navigateBack() {
        history.back();
    }

    getCurrentUrl(): string {
        return location.pathname + location.search;
    }

    setResponseStatus(status: number): void {
        // server side only
    }

    getCurrentUrlPath(): string {
        return location.pathname;
    }

    getQueryParams() {
        return Object.fromEntries(new URLSearchParams(location.search));
    }

    onUrlUpdate(callback: Function) {
        onUrlUpdate.callback = callback;
    }
}

factory('Platform', Platform, () => new Platform());

export { Platform }