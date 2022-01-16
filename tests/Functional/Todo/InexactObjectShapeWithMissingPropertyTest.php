<?php

namespace Duck\Types\Tests\Functional\Todo;

use Duck\Types\IncompatibleTypeError;
use Duck\Types\Type;
use PHPUnit\Framework\TestCase;

class InexactObjectShapeWithMissingPropertyTest extends TestCase
{
    /**
     * Todo: is failing!
     * value shouldn't match, as it doesn't have the key 'foo'
     * https://flow.org/en/docs/types/objects/#toc-exact-object-types
     *
     * @test
     */
    public function missingParameter()
    {
        self::expectException(IncompatibleTypeError::class);

        $annotation = '{foo: string}';
        $value = [];

        self::assertFalse(
            Type::is($annotation, $value)
        );
    }

    /**
     * Working for exact shape.
     *
     * @test
     */
    public function missingParameterWhenExact()
    {
        self::expectException(IncompatibleTypeError::class);

        $annotation = '{|foo: string|}';
        $value = [];

        self::assertTrue(
            Type::is($annotation, $value)
        );
    }
}
