export type ResolverAction = (callback: (result: any, error?: any) => void) => void;

class Resolver {
    onSuccess: CallableFunction;
    onError: CallableFunction | null = null;
    onAlways: CallableFunction | null = null;
    result = null;
    lastError = null;
    action: ResolverAction;

    constructor(action: ResolverAction) {
        this.action = action;
    }

    error(onError: CallableFunction) {
        this.onError = onError;
    }

    success(onSuccess: CallableFunction) {
        this.onSuccess = onSuccess;
    }

    always(always: CallableFunction) {
        this.onAlways = always;
    }

    run() {
        const $this = this;
        this.action(function (result: any, error: any) {
            $this.result = result;
            let throwError = false;
            if (error) {
                $this.lastError = error;
                if ($this.onError !== null) {
                    $this.onError(error);
                }
                else {
                    throwError = true;
                }
            } else {
                $this.onSuccess($this.result);
            }
            if ($this.onAlways != null) {
                $this.onAlways();
            }
            if (throwError) {
                throw $this.lastError;
            }
        });
    }

    then(onSuccess: CallableFunction, onError?: CallableFunction, always?: CallableFunction) {
        this.onSuccess = onSuccess;
        if (onError) {
            this.onError = onError;
        }
        if (always) {
            this.onAlways = always;
        }
        this.run();
    }
}

export { Resolver }