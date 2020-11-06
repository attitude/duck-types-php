<?php

namespace Duck\Types;

class Utils {
  /**
   * Retrieves constant by it's name
   *
   * If constant is not defained, default value is returned if provided.
   *
   * @param string $name Constant name
   * @param mixed $default Default value
   * @return mixed
   */
  public static function const(string $name, $default = null) {
    if (!defined($name)) {
      return $default;
    }

    return constant($name);
  }

  /**
   * Retrieves return value of any callable
   *
   * @param callable $function
   * @return string|void
   */
  public static function getReturnType(callable $function) {
    $reflection = new \ReflectionFunction($function);
    $returnType = $reflection->getReturnType();

    if ($returnType) {
      // TODO: Remove check in the future (removed `__toString()`)
      if (!is_callable([$returnType, 'getName'])) {
        return $returnType->__toString();
      }

      // TODO: Change to $returnType->getName() when ready
      return call_user_func([$returnType, 'getName']);
    }
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
}