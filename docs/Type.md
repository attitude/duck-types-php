# Duck\Types\Type


## Implements:
Duck\Types\TypeInterface



## Constants

| Name | Description |
|------|-------------|
|Type::SHOULD_THROW = true|Alias of `true`|

## Methods

| Name | Description |
|------|-------------|
|[Type::all()](#typeall)|Factory that evaluates all \Closure apssed as erguments|
|[Type::any()](#typeany)|Factory that evaluates any \Closure apssed as erguments|
|[Type::for()](#typefor)|Get or set type with annotation or \Closure|
|[Type::is()](#typeis)|Checks given value against the type|
|[Type::pass()](#typepass)|Passes `$value` through if it is compatible with the $type otherwise throws a [new IncompatibleTypeError](https://github.com/attitude/duck-types-php/blob/main/docs/IncompatibleTypeError.md)|
|[Type::shouldThrow()](#typeshouldthrow)|Assertion to test if the type check fails|
|[Type::wrap()](#typewrap)|Wraps existing callable and throws according to result|


---

---

### Type::all()

**Description**


```php
public static all (\Closure $conditions): \Closure
```

Factory that evaluates all \Closure apssed as erguments

\TypeError will be thrown if any of the tests fails.

**Parameters**

* `(\Closure) $conditions`


**Return Values**

`\Closure`




---

### Type::any()

**Description**


```php
public static any (\Closure $conditions): \Closure
```

Factory that evaluates any \Closure apssed as erguments

\TypeError will be thrown only if all of the tests fail.

**Parameters**

* `(\Closure) $conditions`


**Return Values**

`\Closure`




---

### Type::for()

**Description**


```php
public static for (string $name, string|\Closure $type): \Closure
```

Get or set type with annotation or \Closure

**Parameters**

* `(string) $name`: Name alias for the tupe
* `(string|\Closure) $type`: Type annotation or \Closure


**Return Values**

`\Closure`




---

### Type::is()

**Description**


```php
public static is (mixed $value, string $type): bool
```

Checks given value against the type

**Parameters**

* `(mixed) $value`: Value to type check
* `(string) $type`: Type annotation


**Return Values**

`bool`




---

### Type::pass()

**Description**


```php
public static pass (mixed $value, string|\Closure $type, mixed $default): void
```

Passes `$value` through if it is compatible with the $type otherwise throws a [new IncompatibleTypeError](https://github.com/attitude/duck-types-php/blob/main/docs/IncompatibleTypeError.md)

Can also check the `$default` value. Set `DUCK_TYPE_VAlIDATION_IS_ENABLED` constant to `false` to entirelly skip type checking, e.g. in production environment.

**Parameters**

* `(mixed) $value`: Value to pass through (and to validate)
* `(string|\Closure) $type`: Annotation or type validation \Closure
* `(mixed) $default`: ptional value to pass if the `$value` is not set


**Return Values**

`void`


---

### Type::shouldThrow()

**Description**


```php
public static shouldThrow (string $message, callable $test): \IncompatibleTypeError
```

Assertion to test if the type check fails

Returns IncompatibleTypeError for further investigation.

**Parameters**

* `(string) $message`: Message to compare to
* `(callable) $test`: Callable test to invoke


**Return Values**

`\IncompatibleTypeError`




---

### Type::wrap()

**Description**


```php
public static wrap (callable $function, string|null $type, callable|null $shouldThrow): void
```

Wraps existing callable and throws according to result

`$shouldThrow` \Closure must return a `boolean`. Default is to throw when the return value of the `$callable` is `false` or when it is not set to handle built-in functions like {@see \is_int()} or {@see \is_string()}.

**Parameters**

* `(callable) $function`: A function to wrap in \Closure
* `(string|null) $type`: Name to use in \Throwable message
* `(callable|null) $shouldThrow`: Resolves whether to throw according to the result value of $callable.


**Return Values**

`void`


