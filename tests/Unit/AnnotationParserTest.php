<?php

namespace Duck\Types\Tests\Unit;

use Duck\Types\Annotation;
use PHPUnit\Framework\TestCase;

class AnnotationParserTest extends TestCase
{
    protected $annotations = [
        'null',
        'undefined',
        'number',
        'numeric',
        'string',
        'int',
        'float',
        'bool',
        'boolean',
        'true',
        'false',
        'array',
        'object',
        '*',
        'Duck\Types\Tests\Unit',
        '\Duck\Types\Tests\Unit',
        '\Duck\Types\\',
    ];

    /**
     * like:
     * string
     */
    public function simpleTypeAnnotations(): \Generator
    {
        yield 'type: someType' => [
            'annotation' => 'someType',
            'expectedAst' => ['someType']
        ];

        // And now automated ^^

        foreach ($this->annotations as $annotation) {
            yield "type: $annotation" => [
                'annotation' => $annotation,
                'expectedAst' => [$annotation]
            ];
        }
    }

    /**
     * like:
     * ?string
     */
    public function nullableTypeAnnotations(): \Generator
    {
        foreach ($this->annotations as $annotation) {
            yield "nullable type: $annotation" => [
                'annotation' => "?$annotation",
                'expectedAst' => ['null', $annotation]
            ];
        }
    }

    /**
     * like:
     * string[]
     */
    public function arrayTypeAnnotations(): \Generator
    {
        foreach ($this->annotations as $annotation) {

            if ($annotation === '*') {
                // special behavior
                /** @see {Todo\ArrayTypeAnnotationForAstrixTest::arrayTypeAnnotationForAstrix()} */
                continue;
            }

            yield "array type: $annotation" => [
                'annotation' => $annotation . '[]',
                'expectedAst' => [
                    ':array-annotation:',
                    [
                        $annotation
                    ]
                ]
            ];
        }
    }

    /**
     * like:
     * Array<string>
     */
    public function arrayTypeAnnotationsAlternateSyntax(): \Generator
    {
        foreach ($this->annotations as $annotation) {
            yield "array type: $annotation" => [
                'annotation' => "Array<$annotation>",
                'expectedAst' => [
                    ':array-annotation:',
                    [
                        $annotation
                    ]
                ]
            ];
        }
    }

    /**
     * like:
     * string|int
     */
    public function twoUnionTypeAnnotation(): \Generator
    {
        $annotationList = array_values($this->annotations);
        foreach ($annotationList as $index => $annotation) {

            $firstType = $annotation;
            $secondType = $annotationList[++$index] ?? 'null';

            yield "union type: $firstType|$secondType" => [
                'annotation' => "$firstType|$secondType",
                'expectedAst' => [
                    $firstType, $secondType
                ]
            ];
        }
    }

    /**
     * like:
     * string|int|bool
     */
    public function threeUnionTypeAnnotation(): \Generator
    {
        $annotationList = array_values($this->annotations);
        foreach ($annotationList as $index => $annotation) {

            $firstType = $annotation;
            $secondType = $annotationList[++$index] ?? 'null';
            $thirdType = $annotationList[++$index] ?? 'null';

            yield "union type: $firstType|$secondType|$thirdType" => [
                'annotation' => "$firstType|$secondType|$thirdType",
                'expectedAst' => [
                    $firstType, $secondType, $thirdType
                ]
            ];
        }
    }

    public function multipleUnionWithSameTypeAnnotation(): \Generator
    {
        yield "union type: null|null|null" => [
            'annotation' => "null|null|null",
            'expectedAst' => [
                'null', 'null', 'null'
            ]
        ];
    }

    /**
     * like: (and similar)
     * ?string|int
     */
    public function unionCombinedWithNullable(): \Generator
    {
        yield "union type and real null" => [
            'annotation' => 'null|string|int',
            'expectedAst' => [
                'null', 'string', 'int'
            ]
        ];

        yield "union type and nullable no parentheses" => [
            'annotation' => '?string|int',
            'expectedAst' => [
                ['null', 'string'], 'int'
            ]
        ];

        yield "union type and nullable with parentheses" => [
            'annotation' => '(?string)|int',
            'expectedAst' => [
                ['null', 'string'], 'int'
            ]
        ];

        yield "union type and nullable with parentheses around all" => [
            'annotation' => '?(string|int)',
            'expectedAst' => [
                'null', ['string', 'int']
            ]
        ];
    }

    /**
     * like: (and similar)
     * (?string)[]
     */
    public function nullAbleUnionArray(): \Generator
    {
        yield "nullable type array with parentheses" => [
            'annotation' => '(?string)[]',
            'expectedAst' => [
                ':array-annotation:',
                [
                    'null', 'string'
                ]
            ]
        ];

        // This has a complete different effect
        yield "nullable type array no parentheses" => [
            'annotation' => '?string[]',
            'expectedAst' => [
                'null',
                [
                    ':array-annotation:',
                    [
                        'string'
                    ]
                ]
            ]
        ];
    }


    /**
     * like:
     * (string|int)[]
     */
    public function unionArray(): \Generator
    {
        $annotationList = array_values($this->annotations);
        foreach ($annotationList as $index => $annotation) {

            $firstType = $annotation;
            $secondType = $annotationList[++$index] ?? 'null';

            yield "union array type: ($firstType|$secondType)[]" => [
                'annotation' => "($firstType|$secondType)[]",
                'expectedAst' => [
                    ':array-annotation:',
                    [
                        $firstType, $secondType
                    ]
                ]
            ];
        }
    }

    /**
     * @dataProvider simpleTypeAnnotations
     * @dataProvider nullableTypeAnnotations
     * @dataProvider arrayTypeAnnotations
     * @dataProvider twoUnionTypeAnnotation
     * @dataProvider threeUnionTypeAnnotation
     * @dataProvider multipleUnionWithSameTypeAnnotation
     * @dataProvider arrayTypeAnnotationsAlternateSyntax
     * @dataProvider unionCombinedWithNullable
     * @dataProvider nullAbleUnionArray
     * @dataProvider unionArray
     * @test
     */
    public function parseAnnotation(string $annotation, array $expectedAst)
    {
        $actualAst = Annotation::parse($annotation);
        self::assertSame($expectedAst, $actualAst, "The Annotation parsed doesnt match the expected.");
    }

    /**
     * @test
     */
    public function nullableTypeAnnotationsReversed()
    {
        self::expectError();
        Annotation::parse('string?');
    }
}
