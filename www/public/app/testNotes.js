var HomePage = function () {
    this.title = 'Wellcome to my awesome application\'s';
    this.count = 0;
    this.messages = null;
    var any = 'Any\\\' var\\';
    var priv = 'Secret';

    this.__construct = function () {
    };

    this.Increment = function () {
        this.count++;
        this.priv += "Code";
    };

    this.Test = function (argument) {
        return 'Test ' + argument;
    };
};

test = 'Test';