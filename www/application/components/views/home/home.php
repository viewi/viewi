<?php

use Silly\MyApp\Friend;
use Vo\BaseComponent;

class HomePage extends BaseComponent
{
    public string $title = 'Wellcome to my awesome application\'s';
    public int $count = 0;
    public $messages = [];
    protected $any = 'Any\\\' var\\';
    private string $priv = 'Secret';
    public $json = ['Name' => 'My App'];
    public $fruits = ["Orange", "Apple"];
    public $fruits2 = ["orange", "banana", "apple"];
    public $htag = 'h1';
    public $dynamicName = 'UserItem';
    public CountState $countState;
    public string $html = '<b>RAW <span>demo</span></b> some textNode';
    public string $htmlTd = '<td>RAW Html demo 2</td>';
    public ?Friend $friend = null;
    public bool $true = true;
    public bool $false = false;
    public string $attrName = 'title';
    public string $dynamicEvent = '(click)';
    public string $fullName = 'Default Name';

    function __construct(CountState $countState)
    {
        $this->countState = $countState;
        $this->friend = new Friend();
        $this->friend->Name = 'Frien name';
    }

    function Increment($event)
    {
        $this->count++;
        $this->countState->count++;
        $this->json['Name'] = 'New name';
        // $this->count++;
        $this->priv .= "Code";
        $this->fruits[] = "Banana-{$this->count}";
        $tempArray = $this->fruits2;
        $tempArray[] = "Avokado-{$this->count}";
        $this->htmlTd .= "<td>N {$this->count}</td>";
        $this->attrName = 'area';
        $this->dynamicEvent = '(mouseover)';
        // echo $this->count;
        // echo $event;

        <<<javascript
        var div = document.getElementById('customJsTestId');
        div.innerHTML = "Custom js code " 
            + this.count;
        javascript;
    }

    public function Test($argument): string
    {
        return 'Test ' . $argument;
    }

    public function GetCount(): int
    {
        return $this->count;
    }
}

$test = 'Test';
