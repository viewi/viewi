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
}

export { Platform }