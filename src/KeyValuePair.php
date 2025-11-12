<?php

declare(strict_types = 1);

namespace Galaxon\Collections;

/**
 * Super-simple class to encapsulate a key-value pair, both of which can be any type.
 * Used by Dictionary as the internal array item type.
 */
readonly class KeyValuePair
{
    public function __construct(public mixed $key, public mixed $value)
    {
    }
}
