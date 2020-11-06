<?php

namespace Duck\Types;

final class Type implements TypeInterface {
  /** Should thow */
  const SHOULD_THROW = true;

  private static $registry = [];

  /**
   * Registers a primitive type validator
   *
   * When name matches default validator a warning notice is triggered once.
   *
   * Define `DUCK_TYPE_WARN_ABOUT_DEFAULT_VALIDATORS` constant and set it to
   * `false` to hide warning notice.
   *
   * @param string $name Built-in validator to register
   * @return void
   *
   * @see https://flow.org/en/docs/types/primitives/
   *
   */
  protected static function default(string $name): void {
    static $warnOnce;

    if (isset(static::$registry[$name])) {
      return;
    }

    switch ($name) {
      // Existential type only to check whether value is set. Use with caution
      case '*':
        static::$registry[$name] = function ($value = null) {
          if (!isset($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with existential');
          }
        };
      break;

      case 'null':
        static::$registry[$name] = function ($value = null) {
          if ($value !== null) {
            throw new IncompatibleTypeError($value, 'incompatible with null');
          }
        };
      break;

      case 'undefined':
        static::$registry[$name] = function ($value = null) {
          if (isset($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with undefined');
          }
        };
      break;

      case 'number':
        static::$registry[$name] = function($value) {
          if (is_int($value) || is_float($value) || is_double($value)) {
            return;
          }

          throw new IncompatibleTypeError($value, 'incompatible with number');
        };
      break;

      case 'numeric':
        static::$registry[$name] = function($value) {
          if (!is_numeric($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with numeric');
          }
        };
      break;

      case 'string':
        static::$registry[$name] = function ($value) {
          if (!is_string($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with string');
          }
        };
      break;

      case 'int':
        static::$registry[$name] = function ($value) {
          if (!is_int($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with int');
          }
        };
      break;

      case 'float':
        static::$registry[$name] = function ($value) {
          if (!is_float($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with float');
          }
        };
      break;

      case 'bool':
        static::$registry[$name] = function ($value) {
          if (!is_bool($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with bool');
          }
        };
      break;

      case 'boolean':
        static::$registry[$name] = function ($value) {
          if (!is_bool($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with bool');
          }
        };
      break;

      case 'array':
        static::$registry[$name] = function ($value) {
          if (!is_array($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with array');
          }
        };
      break;

      case 'object':
        static::$registry[$name] = function ($value) {
          if (!is_object($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with object');
          }
        };
      break;
    }

    if ($warnOnce || Utils::const('DUCK_TYPE_WARN_ABOUT_DEFAULT_VALIDATORS', true) === false) {
      return;
    }

    if (isset(static::$registry[$name])) {
      trigger_error("Using default type validator for `${name}` type");
    }

    $warnOnce = true;
  }

  /**
   * Passes `$value` through if it is compatible with the $type otherwise throws
   * a new {@see IncompatibleTypeError}
   *
   * Can also check the `$default` value.
   *
   * Set `DUCK_TYPE_VAlIDATION_IS_ENABLED` constant to `false` to entirelly skip
   * validation, e.g. in production environment.
   *
   * @param mixed $value Value to pass through (and to validate)
   * @param string|\Closure $type Annotation or validation \Closure
   * @param mixed $default ptional value to pass if the `$value` is not set
   */
  public static function pass($value = null, $type, $default = null) {
    if (Utils::const('DUCK_TYPE_VAlIDATION_IS_ENABLED', true) === false) {
      return isset($value) ? $value : $default;
    }

    if ($type instanceof \Closure) {
      $validator = $type;
      $name = '[anonymous]';
    } else {
      $validator = Annotation::compile(Annotation::parse($type));
      $name = $type;
    }

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

    if (!isset($value)) {
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
   * Retrieves an already registered type
   *
   * @param string $name Type name alias to retireve
   * @return \Closure
   */
  public static function get(string $name): \Closure {
    static::default($name);

    if (isset(static::$registry[$name])) {
      return static::$registry[$name];
    }

    if (is_callable($name)) {
      $type = type::wrap($name);
      static::set($name, $type);

      return static::$registry[$name];
    }

    // Exact string value
    $maybeStartQuote = $name[0];
    $maybeEndtQuote = substr($name, -1);

    if (
      ($maybeStartQuote === '"' || $maybeStartQuote === "'") &&
      ($maybeEndtQuote === '"' || $maybeEndtQuote === "'") &&
      $maybeStartQuote === $maybeEndtQuote
    ) {
      return function ($value) use ($name) {
        $string = substr($name, 1, -1);

        if ($value !== $string) {
          throw new IncompatibleTypeError($value, "incompatible with string literal ${name}");
        }
      };
    }

    // Exact int number
    if (preg_match('/^\d+$/', $name)) {
      return function ($value) use ($name) {
        $int = (int) $name;

        if ($value !== $int) {
          throw new IncompatibleTypeError($value, "incompatible with int literal ${name}");
        }
      };
    }

    // Exact float number
    if (preg_match('/^\d+\.\d+$/', $name)) {
      return function ($value) use ($name) {
        $float = (float) $name;


        if ($value !== $float) {
          throw new IncompatibleTypeError($value, "incompatible with float literal ${name}");
        }
      };
    }

    throw new \Exception("Type does not exist: `${name}`", ErrorCodes::NOT_FOUND);
  }

  /**
   * Registers a new type
   *
   * @param string $name New type name (alias) to register that can be later
   *                     retrieved by calling {@see self::get()}.
   * @param callable|string $type Validation \Closure, callable to
   *                              {@see self::wrap()}, type name alias or
   *                              Flow annotation.
   * @return void
   *
   * @see https://flow.org/en/docs/types/ Flow Type Annotations for refference
   */
  public static function set(string $name, $type): void {
    if ($name === 'any') {
      throw new \Exception("Using `any` is unsafe and should be avoided whenever possible", ErrorCodes::FORBIDDEN);
    }

    if (is_callable($type)) {
      $reflection = new \ReflectionFunction($type);

      if ($reflection->getNumberOfParameters() !== 1) {
        throw new \Exception("Expectin validator funciton/closure with one parameter", ErrorCodes::METHOD_NOT_ALLOWED);
      }

      if (is_string($type)) {
        static::$registry[$name] = static::wrap($type);
      } else {
        static::$registry[$name] = $type;
      }
    } elseif (is_string($type)) {
      if (!isset(static::$registry[$type])) {
        static::$registry[$type] = Annotation::compile(
          Annotation::parse($type)
        );
      }

      static::$registry[$name] = static::get($type);
    }
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
      $returnType = Utils::getReturnType($shouldThrow);

      if ($returnType !== 'bool') {
        throw new \Exception("`\$shouldThrow` closure must define return type as bool.", ErrorCodes::METHOD_NOT_ALLOWED);
      }
    }

    return function ($value = null) use ($function, $type, $shouldThrow) {
      $result = call_user_func($function, $value);

      $throw = $shouldThrow
        ? $shouldThrow($result)
        : $result === false || !isset($result);

      if ($throw === Type::SHOULD_THROW) {
        throw new IncompatibleTypeError($value, 'incompatible with '.$type);
      }
    };
  }

  /**
   * Assertion to test if the type validation fails
   *
   * @param string $message Message to compare to
   * @param callable $test Callable test to invoke
   * @return mixed
   */
  public static function shouldThrowIncompatibleTypeError(string $message, callable $test) {
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
