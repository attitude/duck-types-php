Duck Types for PHP
==================

If it walks like a duck and talks like a duck, treat it like a duck, even if it’s not a duck — a dynamic typing for PHP inspired by [Flow](https://flow.org/en/) types.

This tool let's you use basic [FLow annotation syntax](https://flow.org/en/docs/types/) to check data flowing through it. It can generate validation [\Closures](https://www.php.net/manual/en/class.closure) (validator) from a Flow annotation.

```php
<?php

use Duck\Types\Type;

// NULL is compatible with nullable boolean
assert(Type::is('?bool', null));

// Assertion failed because NULL is incompatible with string
assert(Type::is('string', null));

// Passing 'default string value' compatible with string
$variable = Type::pass(null, 'string', 'default string value');

// Cannot pass NULL because NULL is incompatible with string
$variable = Type::pass(null, 'string');

```

Jump to:
- [Supported *Flow* annotations](docs/Annotation.md#supported-flow-annotations)
- [Creating a custom reusable type](#creating-a-custom-reusable-type)
- [Usage with PHP 7 expectations](#usage-with-php-7-expectations)
- [Validator \Closures](#validator-closures)
- [Installation](#installation)
- [Public API](https://github.com/attitude/duck-types-php/blob/main/docs/README.md)

TODO:

- [x] Add support for [PHP 7 expectations](https://www.php.net/manual/en/function.assert.php#function.assert.expectations)
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

Creating a custom reusable type
-------------------------------

By registering a type alias you can make it awailable to the type system for later reuse.

A registered type can be later
- used in annotations;
- made nullable prepending `'?'` before the type alias

```php
<?php

use Duck\Types\Type;
use Duck\Types\IncompatibleTypeError;

// Set new type into the type registry:
Type::for('nonNegativeNumber', function ($value = null): bool {
  if (!isset($value) || !(is_float($value) || is_int($value)) || $value < 0) {
    throw new IncompatibleTypeError( $value, 'not a non-negative number');
  }

  return true;
});

// Set new type into the type registry:
Type::for('largerThan100', function ($value = null): bool {
  if ($value <= 100) {
    throw new IncompatibleTypeError( $value, 'not larger than 100');
  }

  return true;
});

Type::shouldThrow(
  'Cannot pass integer literal 10 because integer literal 10 is incompatible with nonNegativeNumber & largerThan100',
  function () {
    $number = Type::pass(10, 'nonNegativeNumber & largerThan100');
    // Issues:
    // - integer literal 10 is not larger than 100
  }
);

// You can even register a new alias for the intersection reusing
// previously registered types:
Type::for('largerThan100Number', 'nonNegativeNumber & largerThan100');

// And use the new alias:
$number = Type::pass(101, 'largerThan100Number'); // Passes OK

// You can even use it with a nullable sign:
$number = Type::pass(null, '?largerThan100Number'); // Passes OK

// And alo pass a default value:
$number = Type::pass(null, '?largerThan100Number', 199.99); // Passes OK

// Default values are checked against the type too:
Type::shouldThrow(
  'Default value is incompatible with ?largerThan100Number',
  function() {
    $number = Type::pass(null, '?largerThan100Number', 99.99);
    // Issues:
    // - double literal 99.989999999999995 is incompatible with null
    // - double literal 99.989999999999995 is not larger than 100
  }
);

```

---

## Usage with PHP 7 expectations

> assert ( mixed $assertion [, Throwable $exception ] ) : bool
> - Checks if assertion is FALSE
> - $assertion in PHP 7 may also be **any expression that returns a value**, which will be executed and the result used to indicate whether the assertion succeeded or failed.

Source: [php.net](https://www.php.net/manual/en/function.assert.php#function.assert.expectations)

An expression can be a function call, so let's use that:

```php
<?php

// Assert failed validation of type `?bool` example:

$error = Type::shouldThrow(
  'integer literal 1 is incompatible with union',
  function () {
    assert(Type::is('?bool', 1);
    // Fails with 2 errors:
    // - integer literal 1 is incompatible with null
    // - integer literal 1 is incompatible with bool
  }
);

// Print all the caught errors
var_dump($error->getMessages());

```

---

Validator `\Closures`
--------------------

Validator is an anonymous functions accepting one optional parameter that throws `IncompatibleTypeError` when the validation fails. Validator is required to return `bool` type also to comply with [assert()](https://www.php.net/manual/en/function.assert.php).

Optional parameter allows closure to pass `null | undefined` to cover scenarios when the value is missing and to control the thrown more meaningful message thatn PHP's built-in one.

### Validator example:

```php
<?php

use Duck\Types\IncompatibleTypeError;

$nonNegativeNumber = function ($value = null): bool {
  if (!isset($value) || !(is_float($value) || is_int($value)) || $value < 0) {
    throw new IncompatibleTypeError( $value, 'not a non-negative number');
  }

  //Always return bool to comply with assert()
  return true;
};
```

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
