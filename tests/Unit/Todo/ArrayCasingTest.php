<?php

namespace Duck\Types\Tests\Unit\Todo;

use Duck\Types\Annotation;
use PHPUnit\Framework\TestCase;

class ArrayCasingTest extends TestCase
{
    /**
     * array<string> is not working as an alias for Array<string>
     *
     * @test
     */
    public function arrayLowercaseTypeAnnotations()
    {
        self::markTestIncomplete();

        $annotation = 'array<string>';

        $expected = [
            ':array-annotation:',
            [
                'string'
            ]
        ];

        $actualAst = Annotation::parse($annotation);
        self::assertSame($expected, $actualAst);
    }

    /**
     * Working
     *
     * @test
     */
    public function arrayUppercaseTypeAnnotations()
    {
        $annotation = 'Array<string>';

        $expected = [
            ':array-annotation:',
            [
                'string'
            ]
        ];

        $actualAst = Annotation::parse($annotation);
        self::assertSame($expected, $actualAst);
    }
}
