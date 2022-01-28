<?php

namespace Viewi;

class ComponentInfo
{
    function fromArray(array &$item)
    {
        $this->Name = $item['Name'];
        $this->Namespace = $item['Namespace'];
        $this->IsComponent = $item['IsComponent'];
        $this->FullPath = $item['FullPath'];
        $this->Inputs = $item['Inputs'] ?? [];
        $this->Dependencies = $item['Dependencies'] ?? [];
        $this->Versions = $item['Versions'] ?? [];

        if (isset($item['ComponentName']))
            $this->ComponentName = $item['ComponentName'];

        if (isset($item['Tag']))
            $this->Tag = $item['Tag'];

        if (isset($item['BuildPath']))
            $this->BuildPath = $item['BuildPath'];

        if (isset($item['RenderFunction']))
            $this->RenderFunction = $item['RenderFunction'];

        if (isset($item['IsSlot']))
            $this->IsSlot = $item['IsSlot'];

        $this->HasInit = $item['HasInit'] ?? false;
        $this->HasMounted = $item['HasMounted'] ?? false;
        $this->HasBeforeMount = $item['HasBeforeMount'] ?? false;
        $this->HasVersions = $item['HasVersions'] ?? false;
        $this->Relative = $item['Relative'] ?? false;
    }

    public string $Name;
    public string $Namespace;
    public string $ComponentName;
    public string $Tag;
    public string $FullPath;
    public string $TemplatePath;
    public string $BuildPath;
    public string $RenderFunction;
    public bool $IsComponent;
    public bool $IsSlot;
    public bool $HasInit;
    public bool $HasMounted;
    public bool $HasBeforeMount;
    public bool $HasVersions;
    public bool $Relative;

    /**
     * 
     * @var array<string,int>
     */
    public array $Inputs;

    /**
     * 
     * @var array<string,array>
     */
    public array $Dependencies;

    /**
     * 
     * @var array<string,array>
     */
    public array $Versions;

    /**
     * 
     * @var mixed Instance from IContainer
     */
    public $Instance;
}
