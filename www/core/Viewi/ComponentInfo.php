<?php

namespace Viewi;

class ComponentInfo
{
    function fromArray(array &$item)
    {
        $this->Name = $item['Name'];
        $this->Namespace = $item['Namespace'];
        $this->IsComponent = $item['IsComponent'];
        $this->Fullpath = $item['Fullpath'];
        $this->Inputs = $item['Inputs'] ?? [];
        $this->Dependencies = $item['Dependencies'] ?? [];

        if ($item['ComponentName'])
            $this->ComponentName = $item['ComponentName'];

        if ($item['Tag'])
            $this->Tag = $item['Tag'];

        if ($item['BuildPath'])
            $this->BuildPath = $item['BuildPath'];

        if ($item['RenderFunction'])
            $this->RenderFunction = $item['RenderFunction'];

        if ($item['IsSlot'])
            $this->IsSlot = $item['IsSlot'];

        $this->hasInit = $item['hasInit'] ?? false;
    }
    public string $Name;
    public string $Namespace;
    public string $ComponentName;
    public string $Tag;
    public string $Fullpath;
    public string $TemplatePath;
    public string $BuildPath;
    public string $RenderFunction;
    public bool $IsComponent;
    public bool $IsSlot;
    public bool $hasInit;
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
}
