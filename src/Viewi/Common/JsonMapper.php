<?php

namespace Viewi\Common;

use DateTime;
use DateTimeZone;
use ReflectionClass;

class JsonMapper
{
    public static function Instantiate(string $type, object $stdObject)
    {
        if ($type === 'DateTime') {
            return new DateTime($stdObject->date, new DateTimeZone($stdObject->timezone));
        }
        $instance = new $type;
        foreach ($stdObject as $key => $value) {
            if (property_exists($instance, $key)) {
                if (is_object($value)) {
                    $reflection = new ReflectionClass($type);
                    $property = $reflection->getProperty($key);
                    $propertyType = $property->getType();
                    if ($propertyType != null) {
                        $typeName = $propertyType->getName();
                        $instance->$key = self::Instantiate($typeName, $value);
                    } else {
                        $instance->$key = $value;
                    }
                } else {
                    $instance->$key = $value;
                }
            }
        }
        return $instance;
    }
}
