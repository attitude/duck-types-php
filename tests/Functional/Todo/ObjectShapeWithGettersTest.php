<?php

namespace Duck\Types\Tests\Functional\Todo;

use Duck\Types\Type;
use PHPUnit\Framework\TestCase;

class ObjectShapeWithGettersTest extends TestCase
{
    /**
     * FEATURE:
     *
     * Currently only everything available via foreach is checked.
     * https://github.com/mhsdesign/duck-types-php/blob/a7e6ddd83c8a95e1f20da84d9ab736eb4f28520f/src/Annotation.php#L651
     * see: foreach ($value as $key => $item) {
     * @test
     */
    public function objectShapeWithPublicGetters()
    {
        self::markTestIncomplete();

        $annotation = '{|foo: string, bar: int|}';
        $value = new class {
            public function getFoo() { return 'baz'; }
            public function getBar() { return 10; }
        };

        self::assertTrue(Type::is($annotation, $value));
    }
}
