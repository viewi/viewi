<?php

namespace Viewi\Components\Context;

use stdClass;
use Viewi\Builder\Attributes\Skip;

#[Skip]
class ProvidesScope
{
    private stdClass $values;

    public function __construct(private ?ProvidesScope $prototype = null)
    {
        $this->values = new stdClass();
    }

    public function __get($name)
    {
        // $objId = spl_object_id($this);
        // echo "[Proto] accessing $name at $objId" . PHP_EOL;
        if (isset($this->values->{$name})) {
            return $this->values->{$name};
        }
        return $this->prototype->{$name} ?? null;
    }

    public function __set($name, $value)
    {
        $this->values->{$name} = $value;
    }
}
