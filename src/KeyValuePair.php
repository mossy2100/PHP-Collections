<?php

declare(strict_types=1);

namespace Galaxon\Collections;

/**
 * Super-simple class to encapsulate a key-value pair, both of which can be any type.
 * Used by Dictionary as the internal array item type.
 *
 * @property mixed $key The key of the pair.
 * @property mixed $value The value of the pair.
 */
readonly class KeyValuePair
{
    /**
     * Create a new KeyValuePair.
     *
     * @param mixed $key The key.
     * @param mixed $value The value.
     */
    public function __construct(public mixed $key, public mixed $value)
    {
    }
}
