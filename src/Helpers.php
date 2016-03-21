<?php

namespace Pyjac\NaijaEmoji;

class Helpers
{
    /**
     * Checks if all keys in an array are in another array and their values are not empty string.
     *
     * @param array $requiredStrings
     * @param array $searchData
     *
     * @return bool
     */
    public static function keysExistAndNotEmptyString($requiredStrings, $searchData)
    {
        foreach ($requiredStrings as $key => $value) {
            if (!self::keyExistAndNotEmptyString($value, $searchData)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if a key is in an array and the value of the key is not an empty string.
     *
     * @param array $key
     * @param array $searchData
     *
     * @return bool
     */
    public static function keyExistAndNotEmptyString($key, $searchData)
    {
        return isset($searchData[$key]) && !empty($searchData[$key]) && is_string($searchData[$key]) && trim($searchData[$key]);
    }
}
