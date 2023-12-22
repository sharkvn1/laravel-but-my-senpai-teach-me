<?php

/**
 * Builds a file path with the appropriate directory separator.
 * @param string $segments,... unlimited number of path segments
 * @return string Path
 */
function file_build_path(...$segments)
{
    return join(DIRECTORY_SEPARATOR, $segments);
}

/**
 * Convert string to camelCase
 *
 * @param string
 * @return string
 */
function convertCamelCase($str): string
{
    $str = str_replace('_', ' ', strtolower($str));
    $str = ucwords($str);
    $str = str_replace(' ', '', $str);
    return lcfirst($str);
}

/**
 * Unset keys from array
 *
 * @param array $array
 * @param array $keys
 * @return void
 */
function unsets(array &$array, array $keys): void
{
    foreach ($keys as $key) {
        unset($array[$key]);
        unset($array[array_search($key, $array)]);
    }
}

/**
 * Pick keys from array
 *
 * @param array $array
 * @param array $keys
 * @return void
 */
function picks(array &$array, array $keys): void
{
    $inverseKeys = array_diff(array_keys($array), $keys);
    unsets($array, $inverseKeys);
}

/**
 * Check if array element is in another array
 *
 * @param array $array
 * @param array $elements
 * @return bool
 */
function isArrayInArray(array $array, array $elements): bool
{
    return count(array_intersect($array, $elements)) === count($elements);
}

/**
 * Filter array/collection with options
 *
 * @param array|object $array
 * @param array|string|int $keys Array of keys to remove/keep
 * @param bool $inverse If true, return array with elements in $keys, otherwise remove
 * @return array|object
 */
function filterWithOptions(
    array|object $array,
    array|string|int $keys,
    bool $inverse = false
): array|object {

    if (!is_array($keys)) {
        $keys = [$keys];
    }

    $checkKeys = array_map(fn ($key, $value) => is_string($key)
        ? head(explode('.', $key))
        : $value, array_keys($keys), $keys);

    $filtered = collect($array)->filter(
        fn ($value, $key) => $inverse
            ? in_array($key, $checkKeys)
            : !in_array($key, $checkKeys)
    );

    if ($inverse && array_keys($keys) !== range(0, count($keys) - 1)) {
        array_walk($keys, function (&$value, $key) use (&$filtered) {
            if (is_string($key)) {
                if (!strpos($key, '.')) {
                    // change filtered key to value
                    $filtered[$value] = $filtered[$key];
                    unset($filtered[$key]);
                } else {
                    // change filtered key to dot notation key
                    $filtered[$value] = data_get($filtered, $key);
                    unset($filtered[head(explode('.', $key))]);
                }
            }
        });
    }

    return is_object($array) ? $filtered : $filtered->toArray();
}

/**
 * Get array/collection with elements
 *
 * @param array|object $array
 * @param array|string|int $keys Array of keys to keep
 * @return array|object
 */
function arrayWith(array|object $array, array|string|int $keys): array|object
{
    return filterWithOptions($array, $keys, true);
}

/**
 * Get array/collection without elements
 *
 * @param array|object $array
 * @param array|string|int $keys Array of keys to remove
 * @return array|object
 */
function arrayWithout(array|object $array, array|string|int $keys): array|object
{
    return filterWithOptions($array, $keys);
}

/**
 * Check if array is multidimensional
 *
 * @param array $array
 * @return bool
 */
function isMultiDimensional(array $array): bool
{
    return count(array_filter($array, 'is_array')) > 0;
}
