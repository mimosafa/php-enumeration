<?php

declare(strict_types=1);

namespace Enumeration\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ExcludeConstants
{
    /**
     * @var string[]
     */
    public array $names;

    public function __construct(string ...$names)
    {
        $this->names = $names;
    }
}
