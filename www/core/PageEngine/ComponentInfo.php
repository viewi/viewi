<?php

class ComponentInfo
{
    function fromArray(array &$item)
    {
        $this->Name = $item['Name'];
        $this->ComponentName = $item['ComponentName'];
        $this->Tag = $item['Tag'];
        $this->Fullpath = $item['Fullpath'];
        $this->BuildPath = $item['BuildPath'];
        $this->RenderFunction = $item['RenderFunction'];
        $this->ItsSlot = $item['ItsSlot'];
        $this->Inputs = $item['Inputs'] ?? [];
    }
    public string $Name;
    public string $ComponentName;
    public string $Tag;
    public string $Fullpath;
    public string $TemplatePath;
    public string $BuildPath;
    public string $RenderFunction;
    public bool $ItsSlot;
    public array $Inputs;
}
