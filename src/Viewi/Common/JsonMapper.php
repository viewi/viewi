<?php

namespace Viewi\Common;

use DateTime;
use DateTimeZone;
use ReflectionClass;

class JsonMapper
{
    // TODO: bulk mapper, cache type
    public static function Instantiate(string $type, object $stdObject, $instance = null)
    {
        if ($type === 'DateTime') {
            return new DateTime($stdObject->date, new DateTimeZone($stdObject->timezone));
        }
        $instance = $instance ?? new $type;
        foreach ($stdObject as $key => $value) {
            if (property_exists($instance, $key)) {
                if (is_object($value)) {
                    $reflection = new ReflectionClass($type);
                    $property = $reflection->getProperty($key);
                    $propertyType = $property->getType();
                    if ($propertyType != null) {
                        $typeName = $propertyType->getName();
                        $instance->$key = self::Instantiate($typeName, $value, $instance->$key);
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
