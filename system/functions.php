<?php
/**
 * Functions - small collection of Framework wide interest functions.
 *
 * @author Virgil-Adrian Teaca - virgil@giulianaeassociati.com
 * @version 3.0
 * @date April 12th, 2016
 */

/** Array helpers. */

/**
 * Add an element to an array if it doesn't exist.
 *
 * @param  array   $array
 * @param  string  $key
 * @param  mixed   $value
 * @return array
 */
function array_add($array, $key, $value)
{
    if ( ! isset($array[$key])) $array[$key] = $value;

    return $array;
}

/**
 * Build a new array using a callback.
 *
 * @param  array  $array
 * @param  \Closure  $callback
 * @return array
 */
function array_build($array, Closure $callback)
{
    $results = array();

    foreach ($array as $key => $value) {
        list($innerKey, $innerValue) = call_user_func($callback, $key, $value);

        $results[$innerKey] = $innerValue;
    }

    return $results;
}

/**
 * Divide an array into two arrays. One with keys and the other with values.
 *
 * @param  array  $array
 * @return array
 */
function array_divide($array)
{
    return array(array_keys($array), array_values($array));
}

/**
 * Flatten a multi-dimensional associative array with dots.
 *
 * @param  array   $array
 * @param  string  $prepend
 * @return array
 */
function array_dot($array, $prepend = '')
{
    $results = array();

    foreach ($array as $key => $value) {
        if (is_array($value)) {
            $results = array_merge($results, array_dot($value, $prepend.$key.'.'));
        } else {
            $results[$prepend.$key] = $value;
        }
    }

   return $results;
}

/**
 * Get all of the given array except for a specified array of items.
 *
 * @param  array  $array
 * @param  array  $keys
 * @return array
 */
function array_except($array, $keys)
{
    return array_diff_key($array, array_flip((array) $keys));
}

/**
 * Fetch a flattened array of a nested array element.
 *
 * @param  array   $array
 * @param  string  $key
 * @return array
 */
function array_fetch($array, $key)
{
    foreach (explode('.', $key) as $segment) {
        $results = array();

        foreach ($array as $value) {
            $value = (array) $value;

            $results[] = $value[$segment];
        }

        $array = array_values($results);
    }

    return array_values($results);
}

/**
 * Return the first element in an array passing a given truth test.
 *
 * @param  array    $array
 * @param  Closure  $callback
 * @param  mixed    $default
 * @return mixed
 */
function array_first($array, $callback, $default = null)
{
    foreach ($array as $key => $value) {
        if (call_user_func($callback, $key, $value)) return $value;
    }

    return value($default);
}

/**
 * Return the last element in an array passing a given truth test.
 *
 * @param  array    $array
 * @param  Closure  $callback
 * @param  mixed    $default
 * @return mixed
 */
function array_last($array, $callback, $default = null)
{
    return array_first(array_reverse($array), $callback, $default);
}

/**
 * Flatten a multi-dimensional array into a single level.
 *
 * @param  array  $array
 * @return array
 */
function array_flatten($array)
{
    $return = array();

    array_walk_recursive($array, function($x) use (&$return) { $return[] = $x; });

    return $return;
}

/**
 * Get an item from an array using "dot" notation.
 *
 * @param  array   $array
 * @param  string  $key
 * @param  mixed   $default
 * @return mixed
 */
function array_get($array, $key, $default = null)
{
    if (is_null($key)) return $array;
    if (isset($array[$key])) return $array[$key];
    foreach (explode('.', $key) as $segment) {
        if (! is_array($array) || ! array_key_exists($segment, $array)) {
            return $default;
        }
        $array = $array[$segment];
    }
    return $array;
}

/**
 * Set an array item to a given value using "dot" notation.
 *
 * If no key is given to the method, the entire array will be replaced.
 *
 * @param  array   $array
 * @param  string  $key
 * @param  mixed   $value
 * @return array
 */
function array_set(&$array, $key, $value)
{
    if (is_null($key)) return $array = $value;

    $keys = explode('.', $key);

    while (count($keys) > 1) {
        $key = array_shift($keys);

        if ( ! isset($array[$key]) || ! is_array($array[$key])) {
            $array[$key] = array();
        }

        $array =& $array[$key];
    }

    $array[array_shift($keys)] = $value;

    return $array;
}

/**
 * Get a subset of the items from the given array.
 *
 * @param  array  $array
 * @param  array  $keys
 * @return array
 */
function array_only($array, $keys)
{
    return array_intersect_key($array, array_flip((array) $keys));
}

/**
 * Remove an array item from a given array using "dot" notation.
 *
 * @param  array   $array
 * @param  string  $key
 * @return void
 */
function array_forget(&$array, $key)
{
    $keys = explode('.', $key);

    while (count($keys) > 1) {
        $key = array_shift($keys);

        if ( ! isset($array[$key]) || ! is_array($array[$key])) {
            return;
        }

        $array =& $array[$key];
    }

    unset($array[array_shift($keys)]);
}

/**
 * Pluck an array of values from an array.
 *
 * @param  array   $array
 * @param  string  $value
 * @param  string  $key
 * @return array
 */
function array_pluck($array, $value, $key = null)
{
    $results = array();

    foreach ($array as $item) {
        $itemValue = is_object($item) ? $item->{$value} : $item[$value];

        // If the key is "null", we will just append the value to the array and keep
        // looping. Otherwise we will key the array using the value of the key we
        // received from the developer. Then we'll return the final array form.
        if (is_null($key)) {
            $results[] = $itemValue;
        } else {
            $itemKey = is_object($item) ? $item->{$key} : $item[$key];

            $results[$itemKey] = $itemValue;
        }
    }

    return $results;
}

/**
 * Get a value from the array, and remove it.
 *
 * @param  array   $array
 * @param  string  $key
 * @param  mixed   $default
 * @return mixed
 */
function array_pull(&$array, $key, $default = null)
{
    $value = array_get($array, $key, $default);

    array_forget($array, $key);

    return $value;
}