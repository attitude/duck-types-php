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
  public static function getReturnType(\ReflectionFunction $reflection) {
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
   * Asserts valid type
   *
   * @param callable $callable
   * @param string $name
   * @return void
   */
  public static function assertCallable(callable $callable, string $name = '[anonymous]') {
    $reflection = new \ReflectionFunction($callable);

    if ($reflection->getNumberOfParameters() !== 1) {
      throw new \Exception("Expecting ${name} to be a function/closure with one parameter", ErrorCodes::METHOD_NOT_ALLOWED);
    }

    if (Utils::getReturnType($reflection) !== 'bool') {
      throw new \Exception("Expecting ${name} to be a function/closure to return bool", ErrorCodes::METHOD_NOT_ALLOWED);
    }
  }

  public static function missingArrayValues(array $haystack, array $expected) {
    return array_diff($expected, $haystack);
  }
}