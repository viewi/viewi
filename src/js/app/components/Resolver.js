class Resolver {
    onSuccess = null;
    onError = null;
    onAlways = null;
    result = null;
    lastError = null;
    action = null;

    constructor(action) {
        var $this = this;
        $this.action = action;
    }

    error(onError) {
        var $this = this;
        $this.onError = onError;
    }

    success(onSuccess) {
        var $this = this;
        $this.onSuccess = onSuccess;
    }

    always(always) {
        var $this = this;
        $this.onAlways = always;
    }

    then(onSuccess, onError, always) {
        var $this = this;
        if (onError !== null) {
            $this.onError = onError;
        }
        if (always !== null) {
            $this.onAlways = always;
        }
        var throwError = false;
        try {
            $this.result = $this.action();
            onSuccess($this.result);
        }
        catch (ex) {
            $this.lastError = ex;
            if ($this.onError !== null) {
                $this.onError(ex);
            }
            else {
                throwError = true;
            }
        }
        if ($this.onAlways != null) {
            $this.onAlways();
        }
        if (throwError) {
            throw $this.lastError;
        }
    }
}

export { Resolver }