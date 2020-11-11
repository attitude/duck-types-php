<?php

namespace Duck\Types;

final class Registry implements RegistryInterface {
  private static $registry = [];
  private static $autoloaders = [];

  /**
   * Returns a validator for a built-in type
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
  private static function primitive(string $name): \Closure {
    switch ($name) {
      case '*':
        // Existential type only to check whether value is set. Use with caution
        return function ($value = null): bool {
          if (!isset($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with existential');
          }

          return true;
        };
      break;

      case 'null':
        return function ($value = null): bool {
          if ($value !== null) {
            throw new IncompatibleTypeError($value, 'incompatible with null');
          }

          return true;
        };
      break;

      case 'undefined':
        return function ($value = null): bool {
          if (isset($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with undefined');
          }

          return true;
        };
      break;

      case 'number':
        return function ($value = null): bool {
          if (is_int($value) || is_float($value) || is_double($value)) {
            return true;
          }

          throw new IncompatibleTypeError($value, 'incompatible with number');
        };
      break;

      case 'numeric':
        return function ($value = null): bool {
          if (!is_numeric($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with numeric');
          }

          return true;
        };
      break;

      case 'string':
        return function ($value = null): bool {
          if (!is_string($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with string');
          }

          return true;
        };
      break;

      case 'int':
        return function ($value = null): bool {
          if (!is_int($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with int');
          }

          return true;
        };
      break;

      case 'float':
        return function ($value = null): bool {
          if (!is_float($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with float');
          }

          return true;
        };
      break;

      case 'bool':
        return function ($value = null): bool {
          if (!is_bool($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with bool');
          }

          return true;
        };
      break;

      case 'boolean':
        return function ($value = null): bool {
          if (!is_bool($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with bool');
          }

          return true;
        };
      break;

      case 'true':
        return function ($value = null): bool {
          if ($value !== true) {
            throw new IncompatibleTypeError($value, 'incompatible with bool');
          }

          return true;
        };
      break;

      case 'false':
        return function ($value = null): bool {
          if ($value !== false) {
            throw new IncompatibleTypeError($value, 'incompatible with bool');
          }

          return true;
        };
      break;

      case 'array':
        return function ($value = null): bool {
          if (!is_array($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with array');
          }

          return true;
        };
      break;

      case 'object':
        return function ($value = null): bool {
          if (!is_object($value)) {
            throw new IncompatibleTypeError($value, 'incompatible with object');
          }

          return true;
        };
      break;
    }

    throw new \Exception("Primitive type `${name} is not implemented`", ErrorCodes::NOT_IMPLEMENTED);
  }

  /**
   * Returns a type validator for literal value
   *
   * @param string $literal
   * @return \Closure
   */
  private static function literal(string $literal): \Closure {
    // Exact string value in quotes
    $maybeStartQuote = $literal[0];
    $maybeEndtQuote = substr($literal, -1);

    if (
      ($maybeStartQuote === '"' || $maybeStartQuote === "'") &&
      ($maybeEndtQuote === '"' || $maybeEndtQuote === "'") &&
      $maybeStartQuote === $maybeEndtQuote
    ) {
      $quote = $maybeStartQuote;

      $noEscapedQuotes = str_replace('\\'.$quote, '', substr($literal, 1, -1));

      if (strpos($noEscapedQuotes, $quote) === false) {
        return function ($value) use ($literal): bool {
          $string = substr($literal, 1, -1);

          if ($value !== $string) {
            throw new IncompatibleTypeError($value, "incompatible with string literal ${literal}");
          }

          return true;
        };
      }

      throw new \Exception("Unable to parser strin literal ${literal}", ErrorCodes::INTERNAL);
    }

    // Exact int number
    if (preg_match('/^\d+$/', $literal)) {
      return function ($value) use ($literal): bool {
        $int = (int) $literal;

        if ($value !== $int) {
          throw new IncompatibleTypeError($value, "incompatible with int literal ${literal}");
        }

        return true;
      };
    }

    // Exact float number
    if (preg_match('/^\d+\.\d+$/', $literal)) {
      return function ($value) use ($literal): bool {
        $float = (float) $literal;

        if ($value !== $float) {
          throw new IncompatibleTypeError($value, "incompatible with float literal ${literal}");
        }

        return true;
      };
    }

    throw new \Exception('Unable to parse literal ${name}', ErrorCodes::NOT_IMPLEMENTED);
  }

  /**
   * Registers a type-autoloader method to resolve loading of type.
   * 
   * Autoload function should try to set the requested type by calling
   * `Type:for($name, $annotationOrClosure)` inside the type-autoload function.
   *
   * @param string $name Autoloader anme to register
   * @param \Closure $resolver Callable that is called when trying to find out
   *                           whether the type might exist. Callable should
   *                           expect one paramenter, the name of the type.
   * @return void
   */
  public static function registerAutoloader(string $name, callable $resolver): void {
    $reflection = new \ReflectionFunction($resolver);

    if ($reflection->getNumberOfParameters() !== 1) {
      throw new \Exception("Expecting `${name}` autoloader to be a function/closure with one parameter", ErrorCodes::METHOD_NOT_ALLOWED);
    }

    static::$autoloaders[$name] = $resolver;
  }

  /**
   * Checks if the type is already registered
   * 
   * Difference between `Registry::exists()` is that this method escapess
   * all autoloader, even the built-in.
   *
   * @param string $name
   * @return boolean
   */
  public static function has(string $name): bool {
    if (isset(static::$registry[$name])) {
      return true;
    }

    return false;
  }

  /**
   * Checks if type exists in registry & tries to load it if not
   *
   * Uses autoload registerd using `Registry::registerAutoloader()`.
   *
   * **IMPORTANT:** Do not call this method inside any type-autoload function.
   * If yu need to check if the type was already registered, use `Registry::has()`
   *
   * @param string $name Type name to check
   * @return bool
   */
  public static function exists(string $name): bool {
    if (isset(static::$registry[$name])) {
      return true;
    }

    // LAZY LOAD:

    // Maybe it's a primitive type
    try {
      Registry::set($name, Registry::primitive($name));

      return true;
    } catch (\Throwable $th) {
    }

    // Maybe it's a literal type
    try {
      Registry::set($name, Registry::literal($name));

      return true;
    } catch (\Throwable $th) {
    }

    try {
      foreach(static::$autoloaders as $autoloaders) {
        $autoloaders($name);
      }
    } catch (\Throwable $th) {
    }

    if (isset(static::$registry[$name])) {
      return true;
    }

    return false;
  }

   /**
   * Registers a new type
   *
   * @param string $name New type name (alias) to register that can be later
   *                     retrieved by calling [self::get()](#registryget).
   * @param callable|string $type Validation \Closure, callable to
   *                              [self::wrap()](#registryget), type name alias or
   *                              Flow annotation.
   * @return \Closure
   *
   * @see https://flow.org/en/docs/types/ Flow Type Annotations for refference
   */
  public static function set(string $name, \Closure $type): \Closure {
    if ($name === 'any') {
      throw new \Exception("Using `any` is unsafe and should be avoided whenever possible", ErrorCodes::FORBIDDEN);
    }

    return static::$registry[$name] = $type;
  }

  /**
   * Retrieves an already registered type by it's name, literal validators
   *
   * @param string $name Type name alias or type annotation to retireve
   * @return \Closure
   */
  public static function get(string $name): \Closure {
    if (Registry::exists($name)) {
      return static::$registry[$name];
    }

    throw new \Exception("Type does not exist: `${name}`", ErrorCodes::NOT_FOUND);
  }
}
