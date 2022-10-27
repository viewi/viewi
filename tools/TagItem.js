const root = {
    div: [
        "some text"
    ]
};
let t = {};
// <div></div>
// ["div"]

// <i/>
// {i:0}

// ["i"]

// <div>some text</div>
// {div:["some text"]}
// ["div",["some text"]]

// <$tag>some text</$tag>
// {"e":"_component.tag","c":["some text"]}

// <$tag><div>div text</div> some text</$tag>
// {"_":"_component.tag","c":[{div:["div text"]},"some text"]}

// <div><div>div text</div> some text</div>
// {"div":[{div:["div text"]},"some text"]}

// <a href="/link">My link</a>
// {"a":["My link"],"$":[{"href":"/link"}]}

// <div title="Title">My link</div>
// {"div":["My link"],"$":[{"title":"Title"}]}

// <div title="Title" data-id="5" class="_component.getClasses()">My link</div>
// ["div",["My link"],[["title","Title"],["data-id","5"],["class",["_component.getClasses()"]]]

// <div class="_component.getClasses()">Click here<a href="/link">My link</a></div>
// ["div",["Click here",["a",["My link"],[["href","/link"]]]],[["class",["_component.getClasses()"]]]]
// {"div":["Click here",{"a":["My link"],"$":[{"href":"/link"}]}],"$":[{"class":{"_":"_component.getClasses()"}}]}

// <$tag><div>div text</div> some text</$tag>
// [["$tag"],[["div"["div text"]]],"some text"]]

// <div><!-- My comment --></div>
// ["div",0,0,[" My comment "]]

// <div><!-- My comment --></div>
// ["div",[[" My comment ",1]]]

// <div><script itsRaw>raw script</script></div>
// ["div",[["<script itsRaw>raw script</script>",2]]]

// child = text | 
//              [
//              tag | [expression] | [content, type] | 0,
//              child[] | 0 | (none),
//              attribute[] | 0 | (none), // attribute = [name | [expression], ... value | [expression] | (none)]
//              raw | 0 | (none),         // raw = html string
//              comment[] | (none),       // comment   = [content | [expression]]   
//              ]

// children[0] - tag
// children[1] - children
// children[2] - attributes

// expression = "code", subscription[] | (none)
// 


// ["h1",[["title",[["test "],[["_component.checkbox ? 'true':'false'"]],[" rest"]]],["Checkbox"]]]

// generated:
// {"nodes":[[["Layout",["\r\n    ",["Container",["\r\n        ",["h1",["Checkbox"],[["title",["test ",["_component.checkbox ? 'true':'false'"]," rest"]]]],"\r\n        ",["Row",["\r\n            ",["Checkbox",0,[["value",[["_component.checkbox"]]],["label",["Checkbox: ",["_component.checkbox ? 'true':'false'"]]],["(click)",[["_component.onClick(event)"]]]]],"\r\n        "]],"\r\n        ",[" My comment ",1],"\r\n\r\n        ",["div",0,[["style",["min-height: 300px;"]]]],"\r\n    "]],"\r\n"],[["title",["Alerts"]]]]]]}
// <Layout title="Alerts">\n    <Container>\n        <h1 title="test {$checkbox ? 'true' : 'false'} rest">Checkbox</h1>\n        <Row>\n            <Checkbox value="$checkbox" label="Checkbox: {$checkbox ? 'true' : 'false'}" (click)="onClick($event)" />\n        </Row>\n        \x3C!-- My comment -->\n\n        <div style="min-height: 300px;"></div>\n    </Container>\n</Layout>

["button",0,[
    [/* attr 1*/
        ["_component.clickEvent",["this.clickEvent"]],["onClick()"]
    ]/* END attr 1*/
]]