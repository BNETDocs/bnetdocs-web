<?php

namespace BNETDocs\Libraries\Core;

class ArrayTypeCheck
{
    /**
     * Checks a particular array item to ensure it is of a particular type.
     *
     * @param mixed $instance The array item to check.
     * @param string $type The expected type for the array item.
     * @return bool Whether the item is of the particular type, or false if not.
     */
    protected static function check(mixed $instance, string $type): bool
    {
        return (gettype($instance) == $type
            || (is_object($instance) && get_class($instance) == $type)
            || (is_resource($instance) && get_resource_type($instance) == $type));
    }

    /**
     * Checks the keys of an array to ensure all items are of a particular type.
     *
     * @param array $instances The array to iterate and check.
     * @param string $type The expected type for each item in the array.
     * @return bool Whether all items are of the particular type, or false if not.
     */
    public static function keys(array $instances, string $type): bool
    {
        foreach ($instances as $key => $instance)
        {
            if (!self::check($key, $type))
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks keys and values of an array to ensure all items are of a particular type.
     *
     * @param array $instances The array to iterate and check.
     * @param string $type The expected type for each item in the array.
     * @return bool Whether all items are of the particular type, or false if not.
     */
    public static function keysAndValues(array $instances, string $type): bool
    {
        foreach ($instances as $key => $instance)
        {
            if (!self::check($key, $type) && !self::check($instance, $type))
            {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks the values of an array to ensure all items are of a particular type.
     *
     * @param array $instances The array to iterate and check.
     * @param string $type The expected type for each item in the array.
     * @return bool Whether all items are of the particular type, or false if not.
     */
    public static function values(array $instances, string $type): bool
    {
        foreach ($instances as $instance)
        {
            if (!self::check($instance, $type))
            {
                return false;
            }
        }
        return true;
    }
}
