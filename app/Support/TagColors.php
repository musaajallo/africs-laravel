<?php

namespace App\Support;

/**
 * Fixed palette for tag colours — a key stored on the tag, resolved to a
 * pair of CSS custom-property values on the front end.
 */
final class TagColors
{
    public const KEYS = ['gray', 'green', 'amber', 'teal', 'blue', 'violet', 'rose', 'slate'];

    public const DEFAULT = 'gray';
}
