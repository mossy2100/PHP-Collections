<?php

declare(strict_types = 1);

namespace Galaxon\Collections;

readonly class KeyValuePair
{
    public function __construct(public mixed $key, public mixed $value) {}
}
