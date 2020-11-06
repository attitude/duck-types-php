Duck Types for PHP
==================

If it walks like a duck and talks like a duck, treat it like a duck, even if it’s not a duck — a dynamic typing for PHP inspired by [Flow](https://flow.org/en/) types.

This tool let's you use basic [FLow annotation syntax](https://flow.org/en/docs/types/) to check data flowing through it. It can generate validation [\Closures](https://www.php.net/manual/en/class.closure) (validator) from a Flow annotation.

Jump to:
- [Supported *Flow* annotations](#supported-flow-annotations)
- [Using a reusable type](#using-a-reusable-type)
- [Validator \Closures](#validator-closures)
- [Public API](#api)
- [Installation](#installation)

TODO:

- [ ] Add support for [`$Exact<T>` utility type](https://flow.org/en/docs/types/utilities/#toc-exact)
- [ ] Publish asserts for at least some cases

---
---
---

## About

This project is an **experiment turned into a dev-tool**. It is meant to be used during the development and should not be be used in production, so make sure to disable the *duck* validations in production using constant:

```php
define('DUCK_TYPE_VAlIDATION_IS_ENABLED', false);
```

After disabling the validation, value **just flows through** the `Type::pass()` method.

You're not even bound to use the "pass" variable method. It's here to serve as an inspiration and you are encouraged to use your own way how to use this library.

You can just use the `Annotation` methods to `parse()` (some) Flow logic syntax, or use the `compile()` method to generate \Closure validators.

---

Supported Flow annotations
--------------------------

- Primitive types relevant to PHP
   - \* *(exists)*
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
 - Literal types
 - Maybe types
 - Object types
 - Array types
 - Typle types
 - Union types
 - Intersection types

---

Using a reusable type
---------------------

By registering a type alias you can make it awailable to the type system for later reuse.

A registered type can be later
- used in annotations;
- made nullable prepending `'?'` before the type alias

```php
<?php

use Duck\Types\Type;
use Duck\Types\IncompatibleTypeError;

Type::set('nonNegativeNumber', function ($value = null) {
  if (!isset($value) || !(is_float($value) || is_int($value)) || $value < 0) {
    throw new IncompatibleTypeError( $value, 'not a non-negative number');
  }
});

Type::set('largerThan100', function ($value = null) {
  if ($value <= 100) {
    throw new IncompatibleTypeError( $value, 'not larger than 100');
  }
});

Type::shouldThrowIncompatibleTypeError('Cannot pass integer literal 10 because integer literal 10 is incompatible with nonNegativeNumber & largerThan100', function () {
  $price = 10;
  $number = Type::pass($price, 'nonNegativeNumber & largerThan100');
  // Issues:
  // - integer literal 10 is not larger than 100
});

// You can even register a new intersection type::
Type::set('largerThan100Number', 'nonNegativeNumber & largerThan100');

// This passes:
$number = Type::pass(101, 'largerThan100Number');

// You can even make it nullable and also to pass a default value:
$number = Type::pass(null, '?largerThan100Number', 199.99);

// Default values are checked against the type too.
Type::shouldThrowIncompatibleTypeError('Default value is incompatible with ?largerThan100Number', function() {
  $number = Type::pass(null, '?largerThan100Number', 99.99);
  // Issues:
  // - double literal 99.989999999999995 is incompatible with null
  // - double literal 99.989999999999995 is not larger than 100
});

```

---

Validator `\Closures`
--------------------

Anonymous functions with one parameter that throw `IncompatibleTypeError` when the validation fails.

Allow closure to pass null to cover scenario when the value is missing

### Validator example:

```php
<?php

use Duck\Types\IncompatibleTypeError;

$nonNegativeNumber = function ($value = null) {
  if (!isset($value) || !(is_float($value) || is_int($value)) || $value < 0) {
    throw new IncompatibleTypeError( $value, 'not a non-negative number');
  }
};
```

---

API
---

*namespace* `Duck\Types`;

### *final class* `Annotation`

> — *static* function **parse**(`string` **$annotation**): `array`;\
> Parse [supported *Flow* annotations](#supported-annotations) into AST-like tree
>   that can be used in `Annotation::compile()` later
> - `string` **$annotation** — Flow annotation
> - `array` **AST** —-like tree
>
> — *static* function **compile**(`array` **$tree**): `\Closure`;\
> Compiles AST-like tree into validator \Closure
> - `array` **$tree** — AST-like tree genereated with `Annotation::parse()`

### *final class* `Type`

> — *static* function **pass**(**$value** = *null*, **$type**, **$default** = *null*): `mixed`;
>
> Passes `$value` through if it is compatible with the $type otherwise throws
> a new `IncompatibleTypeError`.
>
> Can also check the `$default` value.
>
> Set `DUCK_TYPE_VAlIDATION_IS_ENABLED` constant to `false` to entirelly skip
> validation, e.g. in production environment.
>
> - `mixed` **$value** — Value to pass through (and to validate)
> - `string|\Closure` **$type** — Annotation or validation \Closure
> - `mixed` **$default** — Optional value to pass if the `$value` is not set
>
> — *static* function **set**(string **$name**, **$validator**): void;
>
> Registers a new type
>
> - `string` **$name** — New type name (alias) to register that can be later
>   retrieved by calling {@see self::get()}
> - `callable|string` **$type** — Validation \Closure, callable to Type::wrap(),
>   type name alias or Flow annotation.
>
> — *static* function **get**(`string` **$name**): `\Closure`;
>
> Retrieves an already registered type from registry
>
> - `string` **$name** — Type name alias to retireve
>
> — *static* function **wrap**(callable **$function**, `string` **$type** = *null*): \Closure;
>
> Wraps existing callable and throws according to result
>
> `$shouldThrow` \Closure must return a `boolean`. Default is to throw when
> the return value of the `$callable` is `false` or when it is not set to
> handle built-in functions like [is_int()](https://www.php.net/manual/en/function.is-int) or [is_string()](https://www.php.net/manual/en/function.is-string).
>
> - `callable` **$function** — A function to wrap in \Closure string|null $type Name to use in \Throwable message callable|null $shouldThrow Resolves whether to throw according to result value of $callable.
> - `string|null` **$type** — Name to use in \Throwable message
> - `callable|null` **$shouldThrow** — Resolves whether to throw according to the result value of $callable.
>
> — *static* function **shouldThrowIncompatibleTypeError**(`string` **$message**, `callable` **$test**): `mixed`;
>
> Assertion to test if the type validation fails
>
> - `string` **$message** — Message to compare to
> - `callable` **$test** — Callable test to invoke

### *final class* `IncompatibleTypeError`

> Incompatible type error class
>
> Extends default 'TypeError class. Throw this in custom validation \Closure
> validators.
>
> This class is final. Use composition instead of inheritance.
>
> — function **__construct**(**$given**, `string` **$unexpected**, **$previous** = *null*): `IncompatibleTypeError`;
>
> Class constructor
>
> - `mixed` **$given** — Tested value of escaped string, e.g. object property
> - `string` **$unexpected** — Unexpected condition met, e.g. `'not integer'`
>
> — function **getMessages**(string $path = ''): `string | string[]`;
>
> Retrieves all error messages

---

## Installation

Use one of these 3 options:

#### A/ [Download the latest zip](https://github.com/attitude/duck-types-php/archive/main.zip)

#### B/ Clone with Git

```bash
$ git clone git@github.com:attitude/duck-types-php.git /your/destination/path
```

#### C/ Using [Composer](https://getcomposer.org)

1. Add this to your `composer.json`:
  ```json
  {
    "repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/attitude/duck-types-php"
    }],
    "require": {
      "attitude/duck-types-php": "dev-main"
    }
  }
  ```
2. Run
   ```
   $ composer install
   ```
3. In your code add:

   You will need to require all the files of the librery manuall in case
   you're using Composer, otherwise you can use autoload feature:

   ```php
   <?php
   // When using Composer:
   require_once "vendor/autoload.php"

   use Duck\Types\Type;

   // Register 'hello' string literal type,
   // note the double string quotes:
   Type::set('hello', '"hello"');

   // Use the registered type
   $world = Type::pass('world', 'hello'); // This fails
   $world = Type::pass('world', 'world'); // This works

   ```

✨ ✨ ✨

---

Made with ❤️ to code by [Martin Adamko](https://github.com/attitude)
