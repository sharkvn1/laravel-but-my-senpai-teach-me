<?php

namespace App\Traits;

trait EnumTrait
{
    /**
     * Get all cases from enum class
     *
     * @return array
     */
    public static function toArray(): array
    {
        return static::nameToValueLookup();
    }

    /**
     * Get all values from enum class
     *
     * @param $only List of value or Enum case to get
     * @return array
     */
    public static function values(...$only): array
    {
        if (empty($only)) {
            return array_column(self::cases(), 'value');
        }

        $only = array_map(fn ($item) => is_object($item) ? $item->value : $item, $only);
        return array_intersect(self::values(), $only);
    }

    /**
     * Get all values from enum class
     *
     * @param $excludes List of value or Enum case to exclude
     * @return array
     */
    public static function valuesWithout(...$excludes): array
    {
        $excludes = array_map(fn ($item) => is_object($item) ? $item->value : $item, $excludes);
        return array_diff(self::values(), $excludes);
    }

    /**
     * Get all name (keys) from enum class
     *
     * @param bool $isCamelCase
     * @return array
     */
    public static function keys(bool $isCamelCase = true): array
    {
        return array_map(fn ($item) => $isCamelCase
            ? convertCamelCase($item)
            : $item, array_column(self::cases(), 'name'));
    }

    /**
     * Check if key exists in enum class
     *
     * @param string $key
     * @return bool
     */
    public static function hasKey(string $key): bool
    {
        return in_array($key, self::keys());
    }

    /**
     * Name to value
     *
     * @return array
     */
    public static function nameToValueLookup(): array
    {
        return array_column(self::cases(), 'value', 'name');
    }

    /**
     * Value to name
     *
     * @return array
     */
    public static function valueToNameLookup(): array
    {
        return array_column(self::cases(), 'name', 'value');
    }

    /**
     * Name to enum object
     *
     * @return array
     */
    public static function nameToEnumObjectLookup(): array
    {
        return array_column(self::cases(), null, 'name');
    }

    /**
     * Value to enum object
     *
     * @return array
     */
    public static function valueToEnumObjectLookup(): array
    {
        return array_column(self::cases(), null, 'value');
    }
}
