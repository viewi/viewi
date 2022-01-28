<?php

use Viewi\BaseComponent;
class ConditionalAndForeachRenderingComponent extends BaseComponent
{
    public string $title = "Passing attributes into component";
    /**
     * 
     * @var Memo[]
     */
    public array $memos = [];
    public bool $active = true;
    public int $status = 2;
    public function __init()
    {
        $memo = new Memo();
        $memo->Notes = 'My notes';
        $memo->IsPublished = true;
        $this->memos[] = $memo;

        $memo = new Memo();
        $memo->Notes = 'Second memo, hidden';
        $memo->IsPublished = false;
        $this->memos[] = $memo;

        $memo = new Memo();
        $memo->Notes = 'Third one, published';
        $memo->IsPublished = true;
        $this->memos[] = $memo;
    }
}

class Memo
{
    public string $Notes;
    public bool $IsPublished;
}
