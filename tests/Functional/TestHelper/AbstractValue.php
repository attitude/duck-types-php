<?php

namespace Duck\Types\Tests\Functional\TestHelper;

abstract class AbstractValue
{
    public $value;
    public $description;

    public function __construct(string $description, $value)
    {
        $this->value = $value;
        $this->description = $description;
    }
}
