<?php

namespace Duck\Types;

use Error;
use TypeError;

final class Type implements TypeInterface {
  /** Alias of `true` */
  const SHOULD_THROW = true;

  /**
   * Get or set type with annotation or \Closure
   *
   * @param string|\Closure $type Type annotation or \Closure
   * @return \Closure
   */
  public static function for(string $name, $type = null): \Closure {
    // Getter
    if (!isset($type)) {
      if (Registry::exists($name)) {
        return Registry::get($name, $type);
      }

      return Registry::set($name, Annotation::compile(Annotation::parse($name)));
    }

    // Setter
    if (is_callable($type)) {
      Utils::assertCallable($type, "second function argument `\$type` when setting `${name}`");

      return Registry::set($name, is_string($type)
        ? Type::wrap($type)
        : $type
      );
    }

    if (is_string($type)) {
      return Registry::set(
        $name,
        Registry::exists($type)
          ? Registry::get($type)
          : Annotation::compile(Annotation::parse($type))
      );
    }

    throw new \Exception("Unsupported type of `${type}` parameter supplied. Expecting annotation string, or callable", ErrorCodes::FORBIDDEN);
  }

  /**
   * Passes `$value` through if it is compatible with the $type otherwise throws
   * a [new IncompatibleTypeError](https://github.com/attitude/duck-types-php/blob/main/docs/IncompatibleTypeError.md)
   *
   * Can also check the `$default` value.
   *
   * Set `DUCK_TYPE_VAlIDATION_IS_ENABLED` constant to `false` to entirelly skip
   * type checking, e.g. in production environment.
   *
   * @param mixed $value Value to pass through (and to validate)
   * @param string|\Closure $type Annotation or type validation \Closure
   * @param mixed $default ptional value to pass if the `$value` is not set
   */
  public static function pass($value = null, $type, $default = null) {
    if (Utils::const('DUCK_TYPE_VAlIDATION_IS_ENABLED', true) === false) {
      return isset($value) ? $value : $default;
    }

    $validator = Type::for($type);
    $name = $type instanceof \Closure ? '[anonymous]' : $type;

    if (isset($default)) {
      try {
        $validator($default);
      } catch (IncompatibleTypeError $th) {
        // Local variable that should be visible in trace
        $errors = $th->getMessages();

        throw new IncompatibleTypeError(
          "\\Default value",
          "incompatible with ${name}",
          $th
        );
      }
    }

    if (isset($default) && !isset($value)) {
      return $default;
    }

    try {
      $validator($value);
    } catch (IncompatibleTypeError $th) {
      // Local variable that should be visible in trace
      $errors = $th->getMessages();

      throw new IncompatibleTypeError(
        "\\Cannot pass ".IncompatibleTypeError::getttype($value)." because ".IncompatibleTypeError::getttype($value),
        "incompatible with ${name}",
        $th
      );
    }

    return $value;
  }

  /**
   * Checks given value against the type
   *
   * @param mixed $value Value to type check
   * @param string $type Type annotation
   * @return boolean
   */
  public static function is(string $type, $value = null): bool {
    if (Utils::const('DUCK_TYPE_VAlIDATION_IS_ENABLED', true) === false) {
      return true;
    }

    try {
      (Type::for($type))($value);
    } catch (IncompatibleTypeError $th) {
      // Local variable that should be visible in trace
      $errors = $th->getMessages();

      throw new IncompatibleTypeError(
        "\\Assertion failed because ".IncompatibleTypeError::getttype($value),
        "incompatible with ${type}",
        $th
      );
    }

    return true;
  }

  /**
   * Factory that evaluates all \Closure apssed as erguments
   *
   * \TypeError will be thrown if any of the tests fails.
   *
   * @param \Closure ...$conditions
   * @return \Closure
   */
  public static function all(\Closure ...$conditions): \Closure {
    return function($value = null) use ($conditions) {
      $errors = [];

      foreach ($conditions as $condition) {
        try {
          $condition($value);
        } catch (\Throwable $th) {
          $errors[] = $th->getMessage();
        }
      }

      if (count($errors) === 1) {
        throw new \TypeError(''.implode('', $errors));
      }

      throw new \TypeError('['.implode(' and ', $errors).'] to pass');
    };
  }

  /**
   * Factory that evaluates any \Closure apssed as erguments
   *
   * \TypeError will be thrown only if all of the tests fail.
   *
   * @param \Closure ...$conditions
   * @return \Closure
   */
  public static function any(\Closure ...$conditions): \Closure {
    return function($value = null) use ($conditions) {
      $errors = [];

      foreach ($conditions as $condition) {
        try {
          $condition($value);

          return;
        } catch (\Throwable $th) {
          $errors[] = $th->getMessage();
        }
      }

      if (count($errors) === 1) {
        throw new \TypeError(implode('', $errors));
      }

      throw new \TypeError('one of ['.implode(' or ', $errors).'] to pass.');
    };
  }

  /**
   * Wraps existing callable and throws according to result
   *
   * `$shouldThrow` \Closure must return a `boolean`. Default is to throw when
   * the return value of the `$callable` is `false` or when it is not set to
   * handle built-in functions like {@see is_int()} or {@see is_string()}.
   *
   * @param callable $function A function to wrap in \Closure
   * @param string|null $type Name to use in \Throwable message
   * @param callable|null $shouldThrow Resolves whether to throw according to
   *                                   the result value of $callable.
   *
   * @see \is_int() https://www.php.net/manual/en/function.is-int.php
   * @see \is_string() https://www.php.net/manual/en/function.is-string.php
   *
   */
  public static function wrap(callable $function, string $type = null, callable $shouldThrow = null): \Closure {
    $type = $type
      ? $type
      : preg_replace('/^is_/', '', (new \ReflectionFunction($function))->getName());

    if ($shouldThrow) {
      Utils::assertCallable($shouldThrow, 'third function argument `\$shouldThrow`');
    }

    return function ($value = null) use ($function, $type, $shouldThrow): bool {
      $result = !!call_user_func($function, $value);

      $throw = $shouldThrow
        ? $shouldThrow($result)
        : $result === false || !isset($result);

      if ($throw === Type::SHOULD_THROW) {
        throw new IncompatibleTypeError($value, 'incompatible with '.$type);
      }

      return $result;
    };
  }

  /**
   * Assertion to test if the type check fails
   *
   * Returns IncompatibleTypeError for further investigation.
   *
   * @param string $message Message to compare to
   * @param callable $test Callable test to invoke
   * @return \IncompatibleTypeError
   */
  public static function shouldThrow(string $message, callable $test) {
    try {
      $test();
    } catch (IncompatibleTypeError $th) {
      $multilineMessage = $th->getMessage();

      $multilineMessage = preg_replace('/\/\/.*$/m', '', $multilineMessage);
      $multilineMessage = preg_replace('/^ +/m', '', $multilineMessage);
      $multilineMessage = str_replace("\n", ' ', $multilineMessage);
      $multilineMessage = str_replace("\t", '', $multilineMessage);

      if ($multilineMessage !== $message) {
        var_dump([
          'expected message' => $message,
          'got this message' => $th->getMessage(),
        ]);

        throw new \Exception("Expecting message: ${message}", 0);
      }

      return $th;
    }

    throw new \Exception("Test failed to throw IncompatibleTypeError");
  }
}
