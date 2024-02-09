<?php

namespace App\Service;

/**
 * Responsible for formatting backend data to valid HTML.
 *
 * @package App\Service
 */
class HtmlFormatter
{
    /**
     * Returns a string of HTML attributes.
     * @param array $attrs The keys of this array are the attribute name and the values are the attribute value.
     * @return string String of HTML attributes.
     */
    public static function formatAsAttributes(array $attrs): string {
        return array_reduce(array_keys($attrs), function ($carry, $key) use ($attrs) {
            return $carry . ' ' . $key . '="' . $attrs[$key] . '"';
        }, "");
    }
}