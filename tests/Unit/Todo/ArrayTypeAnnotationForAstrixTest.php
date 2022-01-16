<?php

namespace Duck\Types\Tests\Unit\Todo;

use Duck\Types\Annotation;
use PHPUnit\Framework\TestCase;

class ArrayTypeAnnotationForAstrixTest extends TestCase
{
    /**
     * TODO: unexpected behavior for \*[] unlike Array<\*>
     *
     * Expected result
     *
     * @test
     */
    public function expectedArrayTypeAnnotationForAstrix()
    {
        self::markTestIncomplete();

        $annotation = '*[]';

        $expectedAst = [
            ':array-annotation:',
            [
                '*'
            ]
        ];

        $actualAst = Annotation::parse($annotation);
        self::assertSame($expectedAst, $actualAst, "The Annotation parsed doesnt match the expected.");
    }

    /**
     * The actual result: (not failing ofc)
     *
     * @test
     */
    public function actualArrayTypeAnnotationForAstrix()
    {
        $annotation = '*[]';

        $expectedAst = [
            '*',
            'array'
        ];

        $actualAst = Annotation::parse($annotation);
        self::assertSame($expectedAst, $actualAst, "The Annotation parsed doesnt match the expected.");
    }

    /**
     * Alternate syntax is working
     *
     * @test
     */
    public function alternateArrayTypeAnnotationForAstrix()
    {
        $annotation = 'Array<*>';

        $expectedAst = [
            ':array-annotation:',
            [
                '*'
            ]
        ];

        $actualAst = Annotation::parse($annotation);
        self::assertSame($expectedAst, $actualAst, "The Annotation parsed doesnt match the expected.");
    }
}
