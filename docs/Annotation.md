# Duck\Types\Annotation
Class to support Flow annotations

## Supported Flow annotations:

- Primitive types relevant to PHP
  - `null`
  - `undefined`
  - `number`
  - `numeric`
  - `string`
  - `int`
  - `float`
  - `bool`
  - `boolean`
  - `array`
  - `object`
  - \* *(exists)*
- Literal types for `string`, `int` and `float`
- Maybe types marked with `?` sign, e.g. `?bool`
- Object types, e.g. `{ hello: 'world' }`
- Exact object type, e.g.` {| hello: 'world' |}`
- Array types, e.g. `string[]`
- Tuple types, e.g. `[number, string, 'three']`
- Union types, e.g. `int | float | string`
- Intersection types, e.g. `{ a: int } & {b : float }`
- Grouping with parentheses, e.g. `(int | string)[]`

Type aliases are supported by using {@see \Types::set()} method that can
registers any alias of compiled validator or any \Closure validator.
## Implements:
Duck\Types\AnnotationInterface



## Constants

| Name | Description |
|------|-------------|
|Annotation::UNION_ANNOTATION = :union-annotation:||
|Annotation::INTERSECTION_ANNOTATION = :intersection-annotation:||
|Annotation::SHAPE_ANNOTATION = :shape-annotation:||
|Annotation::ARRAY_ANNOTATION = :array-annotation:||
|Annotation::TUPLE_ANNOTATION = :tuple-annotation:||

## Methods

| Name | Description |
|------|-------------|
|[Annotation::compile()](#annotationcompile)|Compiles AST-like tree into validator \Closure|
|[Annotation::parse()](#annotationparse)|Parses Flow annotation into AST-like tree|


---

---

### Annotation::compile()

**Description**


```php
public static compile (array $tree): \Closure
```

Compiles AST-like tree into validator \Closure

**Parameters**

* `(array) $tree`
: AST-like tree genereated with {@see \Annotation::parse()}

**Return Values**

`\Closure`




---

### Annotation::parse()

**Description**


```php
public static parse (string $annotation): array
```

Parses Flow annotation into AST-like tree

**Parameters**

* `(string) $annotation`
: Flow annotation

**Return Values**

`array`

> AST-like tree


