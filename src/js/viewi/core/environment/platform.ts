import { componentsMeta } from "../component/componentsMeta";
import { handleUrl, onUrlUpdate } from "../router/handleUrl";

class Platform {
    browser: true = true;
    server: false = false;

    constructor() {
    }

    getConfig() {
        return componentsMeta.config;
    }

    redirect(url: string) {
        handleUrl(url);
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

export { Platform }