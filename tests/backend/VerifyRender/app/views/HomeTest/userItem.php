<?php

class UserItem extends BaseComponent
{
    public ?Friend $user = null;
    public bool $active = false;
    public ?string $title = null;
    function __construct()
    {
    }
}
