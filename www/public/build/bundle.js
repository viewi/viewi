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
        priv += "Code";
    };

    this.Test = function (argument) {
         return 'Test ' + argument;
    };

    this.__construct.apply(this,arguments);
};

 var test = 'Test';
var HttpClientService = function () {
    
    this.get = function (url, data) {
         return { message: 'ok' };
    };
};

var NotificationService = function (http) {
    var http = null;
    /**
     * 
     * @var string[]
     */
    this.messages = [];
    var messages2 = [];
    
    this.__construct = function (http) {
         http = http;
        this.messages = [];
        var messages_0 = [];
        messages2 = [];
        var messages2_0 = [];
        this.unknown = messages_0.length + messages2_0.length + this.messages.length + messages2.length;
        this.count = [].length;
    };

    this.Notify = function (message) {
         this.messages.push( message);
    };

    this.Clear = function () {
         this.messages = [];
    };

    this.__construct.apply(this,arguments);
};

 var messages2 = [];
 var h = 7;
 var k = 5;
var AppComponent = function (
        notificationService,
        http,
        name,
        cost,
        notificationService2,
        notificationService3,
        notificationService4
    ) {
    this.about = 'This is php/js page engine';
    this.model = "Page";
    this.url = '';
    this.url2 = '/';
    this.url3 = 'U';
    this.testsArray = ['My test', 'your test'];
    /** 
     * @var Friend[] 
     * */
    this.users = [];
    this.booleans = [true, false];
    this.friend = null;
    var MultiTest = {
        fruits: { a: "orange", b: "banana", c: "apple" },
        numbers: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
        0: [true, false],
        holes: { 0: "first", 5: "second", 6: "third" },
        1: { 0: 1, 1: 1, 2: 1, 3: 13, 4: 1, 8: 1, 9: 19 }
    };
    var friend2 = null;
    this.dynamicTag = 'HomePage';
    this.dynamicAttr = 'data-dynamic';
    this.dynValue = 'Dynamic value';
    this.content = 'Dynamic Content Test';
    this.className = 'app-component';
    this.true = true;
    this.false = false;
    this.html = "<b>raw html - demo</b>";
    this.notificationService = null;
    var http = null;
    
    this.__construct = function (
        notificationService,
        http,
        name,
        cost,
        notificationService2,
        notificationService3,
        notificationService4
    ) {
        var f = arguments.length > 7 ? arguments[7] :  30;
        var test = arguments.length > 8 ? arguments[8] : [5, 6];
        var test2 = arguments.length > 9 ? arguments[9] : [5, 6];
        var test3 = arguments.length > 10 ? arguments[10] : [5, 6];
        var test4 = arguments.length > 11 ? arguments[11] : [5, 6];
        this.notificationService = notificationService;
        http = http;
        this.friend = new Friend();
        friend2 = new Friend();
        this.friend.Name = "Jhon Doe " + this.model;
        this.friend.Age = f;
        friend2.Name = "Jhon Doe " + this.model;
        friend2.Age = f;
        var letters = 'abcdefghijklmnopqrstuvwxyz';
        for (var i = 0; i < 3; i++){
            var user = new Friend();
            // new user
            user.Name = 'Jhon' + letters[26 - i] + ' Doe' + letters[i];
            user.Age = 30 + i;
            this.users[i] = user;
            this.users[friend2.Name] = user;
            this.users["ID-" + i] = user;
            this.users["ID-" + test[1] + "test"] = user;
            this.users["ID-" + friend2.Name + "test"] = user;
        }
        for (var _i in this.users) {
            var user = this.users[_i];
            user.Name;
        }
        for (var id in this.users) {
            var user = this.users[id];
            id + user.Name;
        }
        MultiTest.length;
        letters = 'X';
        test = [5, test];
        f = 98;
        name = 'My name';
        var http_0 = new HttpClientService();
        switch(f){
            case 6: {
                f = 9;
                break;
            }
            default: {
                f = 5;
                break;
            }
        }
        if(f > 0){
            echo f;
            print_r(f);
        }
        else if(f > 5){
            echo f;
            print_r(f);
        }
        else if(f > 10 && this.users.length > 3){
            echo f;
            print_r(f);
        }
    };

    this.getFullName = function () {
         return 'Jhon Doe';
    };

    this.getOccupation = function () {
         var letters = 'X';
        return 'Web developer';
    };

    this.__construct.apply(this,arguments);
};

 var letters = 'X';
var Friend = function () {
    this.Name = null;
    this.Age = null;
};

var UserItem = function () {
    this.user = null;
    this.active = false;
    this.title = null;
    
    this.__construct = function () {
    };

    this.__construct.apply(this,arguments);
};

var Layout = function () {
    this.title = 'This is layout default title';
    
    this.__construct = function () {
    };

    this.__construct.apply(this,arguments);
};

