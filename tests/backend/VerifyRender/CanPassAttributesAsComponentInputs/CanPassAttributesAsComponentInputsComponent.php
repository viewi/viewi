<?php

use Viewi\BaseComponent;
class CanPassAttributesAsComponentInputsComponent extends BaseComponent
{
    public string $title = "Passing attributes into component";
    public Memo $memo1;
    public Memo $memo2;
    public function __init()
    {
        $this->memo1 = new Memo();
        $this->memo1->Notes = 'My notes';
        $this->memo1->IsPublished = true;

        $this->memo2 = new Memo();
        $this->memo2->Notes = 'Second memo';
        $this->memo2->IsPublished = false;
    }
}

class Memo
{
    public string $Notes;
    public bool $IsPublished;
}
