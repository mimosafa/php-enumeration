<?php

declare(strict_types=1);

namespace Enumeration\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class AllowDuplicateValues
{
    public function __construct(public bool $allow = true)
    {
    }
}
