<?php

namespace Duck\Types\Tests\Functional;

use Duck\Types\IncompatibleTypeError;
use Duck\Types\Type;
use PHPUnit\Framework\TestCase;

class SimplePrimitiveTypesTest extends TestCase
{
    public function annotationsWithMatchingValue(): \Generator
    {
        $annotationWithValue = [
            'null' => null,
            'undefined' => @$undefinedVar, // needs error supression, to pass undef var.
            'number' => 100,
            'numeric' => '100',
            'string' => "hello",
            'int' => 42,
            'float' => 33.33,
            'bool' => true,
            'boolean' => false,
            'true' => true,
            'false' => false,
            'array' => [],
            'object' => new class {},
            '*' => "hi"
        ];

        foreach ($annotationWithValue as $annotation => $value) {
            yield "Type check $annotation" => [
                'annotation' => $annotation,
                'value' => $value
            ];
        }
    }

    public function nullableAnnotationsMatchesNull(): \Generator
    {
        $annotationShouldPassNull = [
            '?null', // not so helpful ^^
            '?*', // existential (*) doesnt accept null? Todo\ExistentialNullTest
            '?undefined',
            '?number',
            '?numeric',
            '?string',
            '?int',
            '?float',
            '?bool',
            '?boolean',
            '?true',
            '?false',
            '?array',
            '?object',
        ];

        foreach ($annotationShouldPassNull as $annotation) {
            yield "Type check $annotation" => [
                'annotation' => $annotation,
                'value' => null
            ];
        }
    }


    public function annotationsWithNotMatchingValue(): \Generator
    {
        $annotationWithValue = [
            'null' => 0,
            'undefined' => 'hello',
            'number' => '100',
            'numeric' => 'foo',
            'string' => false,
            'int' => '42',
            'float' => '33.33',
            'bool' => null,
            'boolean' => 'true',
            'true' => 'true',
            'false' => 'false',
            'array' => null,
            'object' => [],
            '*' => @$undefinedVar
        ];

        foreach ($annotationWithValue as $annotation => $value) {
            yield "Type check $annotation" => [
                'annotation' => $annotation,
                'value' => $value
            ];
        }
    }

    /**
     * @test
     * @dataProvider annotationsWithMatchingValue
     * @dataProvider nullableAnnotationsMatchesNull
     */
    public function annotationMatchesValue(string $annotation, $value)
    {
        self::assertTrue(Type::is($annotation, $value));
    }

    /**
     * @dataProvider annotationsWithNotMatchingValue
     * @test
     */
    public function annotationDoesntMatchValue(string $annotation, $value, ?string $exceptionMessage = null, ?int $exceptionCode = null)
    {
        self::expectException(IncompatibleTypeError::class);

        is_null($exceptionMessage) ?: self::expectExceptionMessage($exceptionMessage);
        is_null($exceptionCode) ?: self::expectExceptionCode($exceptionCode);

        Type::is($annotation, $value);
    }
}
