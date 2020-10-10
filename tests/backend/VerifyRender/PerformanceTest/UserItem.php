<?php

use Viewi\BaseComponent;

class UserItem extends BaseComponent
{
    public ?User $user = null;
    public bool $active = false;
    public ?string $title = null;
    function __init()
    {
    }
}
