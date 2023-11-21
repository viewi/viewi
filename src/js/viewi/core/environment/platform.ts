import { componentsMeta } from "../component/componentsMeta";
import { handleUrl } from "../router/handleUrl";

class Platform {
    browser: true = true;
    server: false = false;

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

    getCurrentUrlPath(): string {
        return location.pathname;
    }

    getQueryParams() {
        return Object.fromEntries(new URLSearchParams(location.search));
    }
}

export { Platform }