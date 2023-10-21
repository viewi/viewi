import { PostModel } from "./PostModel";
import { BaseComponent } from "../../viewi/core/component/baseComponent";
import { register } from "../../viewi/core/di/register"
import { Layout } from "./Layout";

var HttpClient = register.HttpClient;

class PostPage extends BaseComponent {
    _name = 'PostPage';
    post = null;
    error = "";
    message = "";
    $http = null;

    constructor(http) {
        super();
        var $this = this;
        $this.$http = http;
    }

    init() {
        var $this = this;
        $this.$http.get("\/api\/post").then(function (post) {
            $this.post = post;
            $this.message = "Post has been read successfully";
        }, function () {
            $this.error = "Server error";
        });
    }
}

export const PostPage_x = [
    function (_component) { return _component.post ? _component.post.name : ""; },
    function (_component) { return "Message: " + (_component.message ?? ""); },
    function (_component) { return "Error: " + (_component.error ?? ""); },
    function (_component) { return _component.post; },
    function (_component) { return "\n            " + (_component.post.id ?? "") + " " + (_component.post.name ?? "") + "\n        "; }
];

export { PostPage }