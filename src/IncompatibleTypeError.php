<?php

namespace Duck\Types;

/**
 * Incompatible type error class
 *
 * Extends default 'TypeError class. Throw this in custom validation \Closure
 * validators.
 *
 * This class is final. Use composition instead of inheritance.
 */
final class IncompatibleTypeError extends \TypeError {
  const INCOMPATIBLE_WITH_UNION = ':incompatible-with-union:';
  const INCOMPATIBLE_WITH_INTERSECTION = ':incompatible-with-intersection:';

  const INCOMPATIBLE_WITH_SHAPE = ':incompatible-with-shape:';
  const INCOMPATIBLE_WITH_EXACT_SHAPE = ':incompatible-with-exact-shape:';
  const INCOMPATIBLE_IN_SHAPE_PROPERTIES = ':incompatible-in-shape-properties:';
  const INCOMPATIBLE_IN_EXACT_SHAPE_PROPERTIES = ':incompatible-in-exact-shape-properties:';

  const INCOMPATIBLE_WITH_ARRAY = ':incompatible-with-array:';
  const INCOMPATIBLE_IN_ARRAY_MEMBERS = ':incompatible-in-array-mebers:';

  const INCOMPATIBLE_WITH_TUPLE = ':incompatible-with-tuple:';
  const INCOMPATIBLE_IN_TUPLE_MEMBERS = ':incompatible-in-tuple-members:';

  /**
   * Type representation of the given tested value
   *
   * @var string
   */
  protected $given;

  /**
   * Unexpected condition that was met, failed validation
   *
   * Result of the validation, e.g. missing key, inmpatible with int, etc.
   *
   * @var string
   */
  protected $unexpected;

  /**
   * List of previously caught incompatible type errros
   *
   * @var IncompatibleTypeError[]
   */
  protected $previous = [];

  /**
   * Checks and sets the previous error tree
   *
   * @param array[\Throwable] $previous
   * @return void
   */
  protected function __setPrevious(array $previous) {
    foreach ((array) $previous as $key => $th) {
      if (!$th instanceof \Throwable) {
        throw new \Exception(gettype($th)."`\$previous` argument in the contructor is incompatible with \Throwable", ErrorCodes::CONFLICT);
      }

      $this->previous[$key] = $th;
    }
  }

  /**
   * Human readable representation of the constant
   *
   * @param string $unexpected
   * @return string
   */
  protected static function __readableExpected(string $unexpected): string {
    switch ($unexpected) {
      case static::INCOMPATIBLE_WITH_UNION:
        return 'incompatible with union';
      break;
      case static::INCOMPATIBLE_WITH_INTERSECTION:
        return 'incompatible with intersection';
      break;

      case static::INCOMPATIBLE_WITH_SHAPE:
        return 'incompatible with shape';
      break;
      case static::INCOMPATIBLE_WITH_EXACT_SHAPE:
        return 'incompatible with exact shape';
      break;

      case static::INCOMPATIBLE_IN_SHAPE_PROPERTIES:
        return 'incompatible in shape properties';
      break;
      case static::INCOMPATIBLE_IN_EXACT_SHAPE_PROPERTIES:
        return 'incompatible in exact shape properties';
      break;

      case static::INCOMPATIBLE_WITH_ARRAY:
        return 'incompatible with array';
      break;
      case static::INCOMPATIBLE_IN_ARRAY_MEMBERS:
        return 'incompatible in array members';
      break;

      case static::INCOMPATIBLE_WITH_TUPLE:
        return 'incompatible with tuple';
      break;
      case static::INCOMPATIBLE_IN_TUPLE_MEMBERS:
        return 'incompatible in tuple members';
      break;
    }

    return $unexpected;
  }

  /**
   * Class constructor
   *
   * @param mixed $given Tested value of escaped string, e.g. object property
   * @param string $unexpected Unexpected condition met `'not integer'`
   * @param IncompatibleTypeError[]|IncompatibleTypeError Previously caught incompatible type error/s
   */
  public function __construct($given, string $unexpected, $previous = null) {
    $given = static::escapedGettype($given);
    $this->given = $given;

    $message = sprintf("%s is %s", $given, static::__readableExpected($unexpected));

    parent::__construct($message, null, $previous instanceof \Throwable ? $previous : null);

    if ($previous) {
      if ($previous instanceof \Throwable) {
        $previous = [$previous];
      }

      $this->__setPrevious($previous);
    }

    $this->unexpected = $unexpected;
  }

  /**
   * Returns unexpected condition met
   *
   * @return string
   */
  public function getUnexpected(): string {
    return $this->unexpected;
  }

  /**
   * Returns type representation of the variable
   *
   * @param mixed $value
   * @return string
   */
  public static function getttype($value): string {
    $type = gettype($value);

    switch ($type) {
      case 'bool':
      case 'int':
      case 'integer':
      case 'double':
      case 'string':
        return "${type} literal ".json_encode($value);
      break;

      case 'array':
      case 'object':
        return "${type} literal";
      break;
    }

    return $type;
  }

  /**
   * Returns variable type except strings with "\\" escape character
   *
   * @see IncompatibleTypeError::getttype()
   *
   * @param mixed $given
   * @return string
   */
  public static function escapedGettype($given): string {
    if (is_string($given) && substr($given, 0, 1) === '\\') {
      return substr($given, 1);
    }

    return static::getttype($given);
  }

  /**
   * Returns passed array or null if the array is empty
   *
   * @param null|array $value
   * @return null|array
   */
  protected static function nullIfEmptyArray(array $value = null) {
    return isset($value) && !(is_array($value) && empty($value)) ? $value : null;
  }

  /**
   * Helper method to introspect the caught errors tree
   *
   * @return void
   */
  public function debug() {
    return [
      'message' => $this->message,
      'given' => $this->given,
      'unexpected' => $this->unexpected,
      'path' => "{$this->file}:{$this->line}",
      'previous' => static::nullIfEmptyArray(array_map(function($th) {
        return $th->debug();
      }, $this->previous)),
    ];
  }

  /**
   * Flattens passed array into one-dimmensional array
   *
   * @param array $array
   * @return array
   */
  public static function flatArray(array $array): array {
    $return = array();

    array_walk_recursive($array, function($a) use (&$return) { $return[] = $a; });

    return $return;
  }

  /**
   * Retrieves all error messages
   *
   * @return string[]|string
   */
  public function getMessages(string $path = '') {
    switch ($this->unexpected) {
      case static::INCOMPATIBLE_WITH_UNION:
        $messages = array_map(function(IncompatibleTypeError $th) use ($path) {
          return $th->getMessages($path) ;
        }, $this->previous);

        $deepErrors = array_filter($messages, 'is_array');

        if ($deepErrors) {
          return $messages;
        }

        $incompatibilities = array_map(function(IncompatibleTypeError $th) {
          return $th->getUnexpected();
        }, $this->previous);

        return "$this->message because {$this->given} is either ".implode(' or ', $incompatibilities);
      break;

      case static::INCOMPATIBLE_WITH_INTERSECTION:
        $messages = array_map(function(IncompatibleTypeError $th) use ($path) {
          return $th->getMessages($path);
        }, $this->previous);

        if (is_string($messages)) {
          throw new \Exception("Logical error", ErrorCodes::INTERNAL);
        }

        return static::flatArray($messages);
      break;

      case static::INCOMPATIBLE_IN_ARRAY_MEMBERS:
        return static::flatArray(array_map(function(IncompatibleTypeError $previous, $key) use ($path) {
          $messages = $previous->getMessages();

          if (is_string($messages)) {
            return "${messages} at index #${key} in array members of property `${path}`";
          }

          throw new \Exception('Logical error.', ErrorCodes::INTERNAL);
        }, $this->previous, array_keys($this->previous)));
      break;

      case static::INCOMPATIBLE_IN_TUPLE_MEMBERS:
        return static::flatArray(array_map(function(IncompatibleTypeError $previous, $key) use ($path) {
          $fullPath = $path ? "${path}.${key}" : $key;
          $messages = $previous->getMessages();

          if (is_string($messages)) {
            return "${messages} at index #${key} in tuple members of property `${path}`";
          }

          throw new \Exception('Logical error.', ErrorCodes::INTERNAL);
        }, $this->previous, array_keys($this->previous)));
      break;

      case static::INCOMPATIBLE_IN_SHAPE_PROPERTIES:
      case static::INCOMPATIBLE_IN_EXACT_SHAPE_PROPERTIES:
        return static::flatArray(array_map(function(IncompatibleTypeError $previous, $key) use ($path) {
          $fullPath = $path ? "${path}.${key}" : $key;
          $messages = $previous->getMessages($fullPath);

          if (is_string($messages)) {
            return ["${messages} in {$this->given} of property `${fullPath}`"];
          }

          return $messages;
        }, $this->previous, array_keys($this->previous)));
      break;

      case static::INCOMPATIBLE_WITH_SHAPE:
      case static::INCOMPATIBLE_WITH_EXACT_SHAPE:
      case static::INCOMPATIBLE_WITH_ARRAY:
      case static::INCOMPATIBLE_WITH_TUPLE:
          return $this->message;
      break;
    }

    $previousCount = count($this->previous);


    if ($previousCount === 0) {
      return $this->message;
    }

    if ($previousCount === 1) {
      return static::flatArray([
        $this->message,
        $this->previous[0]->getMessages(),
      ]);
    }

    var_dump($this->debug());
    var_dump($this->unexpected);
    var_dump($this->previous);

    throw new \Exception("Not implemented `{$this->unexpected}`", ErrorCodes::NOT_IMPLEMENTED);
  }

  /**
   * Retuns the deepest thrown Error
   *
   * Note that recursion will stop at the first error with multiple errors and
   * this error is returned.
   *
   * @return \Throwable
   */
  public function getDeepestPrevious(): \Throwable {
    $maybePrevious = $this->getPrevious();

    if (isset($maybePrevious)) {
      if ($maybePrevious instanceof IncompatibleTypeError) {
        return $maybePrevious->getDeepestPrevious();
      }

      return $maybePrevious->getPrevious();
    }

    return $this;
  }
}
