<?php

namespace Duck\Types;

/**
 * Class to support Flow annotations
 *
 * ## Supported Flow annotations:
 *
 * - Primitive types relevant to PHP
 *   - `null`
 *   - `undefined`
 *   - `number`
 *   - `numeric`
 *   - `string`
 *   - `int`
 *   - `float`
 *   - `bool`
 *   - `boolean`
 *   - `array`
 *   - `object`
 *   - \* *(exists)*
 * - Literal types for `string`, `int` and `float`
 * - Maybe types marked with `?` sign, e.g. `?bool`
 * - Object types, e.g. `{ hello: 'world' }`
 * - Exact object type, e.g.` {| hello: 'world' |}`
 * - Array types, e.g. `string[]`
 * - Tuple types, e.g. `[number, string, 'three']`
 * - Union types, e.g. `int | float | string`
 * - Intersection types, e.g. `{ a: int } & {b : float }`
 * - Grouping with parentheses, e.g. `(int | string)[]`
 *
 * Type aliases are supported by using [Registry::set()](https://github.com/attitude/duck-types-php/blob/main/docs/Registry.md#registryset)
 * method that can
 * registers any alias of compiled validator or any \Closure validator.
 *
 * @see https://flow.org/en/docs/types/ - Flow Type Annotations
 *
 */
final class Annotation implements AnnotationInterface {
  const UNION_ANNOTATION = ':union-annotation:';
  const INTERSECTION_ANNOTATION = ':intersection-annotation:';
  const SHAPE_ANNOTATION = ':shape-annotation:';
  const ARRAY_ANNOTATION = ':array-annotation:';
  const TUPLE_ANNOTATION = ':tuple-annotation:';

  /**
   * Returns available expression or the default union expression
   *
   * @param string|array $item
   * @return void
   */
  protected static function expression($item) {
    if (is_string($item) && in_array($item, [
      Annotation::INTERSECTION_ANNOTATION,
      Annotation::SHAPE_ANNOTATION,
      Annotation::ARRAY_ANNOTATION,
      Annotation::TUPLE_ANNOTATION,
    ])) {
      return $item;
    }

    return Annotation::UNION_ANNOTATION;
  }

  /**
   * Recursive method to convert Flow syntaxt int AST-like tree
   *
   * @param array $tree
   * @return void
   */
  protected static function walk(array &$tree) {
    static $warnOnce;

    // Recursion
    foreach ($tree as $i => &$sub) {
      if (is_array($sub)) {
        static::walk($sub);
      }
    }

    // Exact shape marks
    foreach ($tree as $i => &$sub) {
      if ($sub === '#') {
        $indexer = $tree[$i+1]['indexer'] ?? null;

        if (
          !$warnOnce &&
          $indexer &&
          Utils::const('DUCK_TYPES_WARN_ABOUT_EXACT_SHAPE_INDEXERS', true) !== false
        ) {
          trigger_error("Indexers are usefull usually for inexact shapes");

          $warnOnce = true;
        }

        $shape = isset($tree[$i+1]['shape'])
          ? $tree[$i+1]['shape']
          : [];

        $tree[$i + 1] = [Annotation::SHAPE_ANNOTATION, [
          'exact' => true,
          'indexer' => $indexer,
          'shape' => $shape,
        ]];

        unset($tree[$i]);
      }
    }

    $tree = array_values($tree);

    // Shape marks
    foreach ($tree as $i => &$sub) {
      if ($sub === '@') {
        $indexer = $tree[$i+1]['indexer'] ?? null;
        $shape = isset($tree[$i+1]['shape'])
          ? $tree[$i+1]['shape']
          : [];

        $tree[$i + 1] = [Annotation::SHAPE_ANNOTATION, [
          'exact' => false,
          'indexer' => $indexer,
          'shape' => $shape,
        ]];

        unset($tree[$i]);
      }
    }

    $tree = array_values($tree);

    // Array<> marks
    foreach ($tree as $i => &$sub) {
      if ($sub === '~') {
        $tree[$i + 1] = [Annotation::ARRAY_ANNOTATION, (array) $tree[$i+1]];

        unset($tree[$i]);
      }
    }

    $tree = array_values($tree);

    // Tuple marks
    foreach ($tree as $i => &$sub) {
      if ($sub === '^') {
        $tree[$i + 1] = [Annotation::TUPLE_ANNOTATION, $tree[$i+1]];

        unset($tree[$i]);
      }
    }

    $tree = array_values($tree);

    // xyz[] marks
    foreach ($tree as $i => &$sub) {
      if ($sub === '$') {
        if (is_string($tree[$i - 1]) && strlen($tree[$i - 1]) === 1) {
          $tree[$i] = 'array';
        } else {
          $tree[$i] = [Annotation::ARRAY_ANNOTATION, (array) $tree[$i - 1]];

          unset($tree[$i - 1]);
        }
      }
    }

    $tree = array_values($tree);

    // Optional marks
    foreach ($tree as $i => &$sub) {
      if ($sub === '?') {
        $tree[$i + 1] = array_merge(['null'], [$tree[$i+1]]);

        unset($tree[$i]);
      }
    }

    $tree = array_values($tree);

    // ANDs
    $lastAndIndex = null;

    foreach ($tree as $i => &$sub) {
      if ($sub === '&') {
        if ($tree[$i - 1][0] !== Annotation::INTERSECTION_ANNOTATION) {
          // Move previous
          $tree[$i] = [Annotation::INTERSECTION_ANNOTATION, $tree[$i - 1]];
          unset($tree[$i - 1]);
        } else {
          unset($tree[$i]);
        }

        $lastAndIndex = $i;
      } elseif ($lastAndIndex === $i - 1 && $tree[$i - 1][0] === Annotation::INTERSECTION_ANNOTATION) {
          // Previous was &
          // Add current to group
          $tree[$i - 1][] = $sub;
          // Move previous
          $tree[$i] = &$tree[$i - 1];
          unset($tree[$i - 1]);
      }
    }

    $tree = array_values($tree);

    $lastORIndex = null;

    // ORs
    foreach ($tree as $i => &$sub) {
      if ($sub === '|') {
        if ($lastORIndex === null) {
          $sub = [
            0 => $tree[$i - 1],
          ];
        } else {
          $sub = $tree[$i - 1];
        }

        unset($tree[$i - 1]);

        $lastORIndex = $i;
      } elseif ($sub === ',') {
        $lastORIndex = null;
      } elseif ($lastORIndex === $i - 1) {
        $tree[$i - 1][] = $sub;
        $sub = $tree[$i - 1];

        unset($tree[$i - 1]);

        $lastORIndex = $i;
      }
    }

    $tree = array_values($tree);

    // Commas
    if (in_array(',', $tree, true)) {
      foreach ($tree as $i => &$sub) {
        if ($sub === ',') {
          unset($tree[$i]);
        }
      }
    }

    $tree = array_values($tree);

    // Map object keys
    if (in_array(':', $tree, true)) {
      $key = null;
      $next = [];
      $indexer = null;

      foreach ($tree as $i => &$sub) {
        if ($sub === ':') {
          continue;
        }

        if (isset($tree[$i + 1]) && $tree[$i + 1] === ':') {
          // Simplify tree
          if ($key && sizeof($next[$key]) === 1 && is_array($next[$key])) {
            $next[$key] = $next[$key][0];
          }

          // Mark optionality
          // if (strstr($sub, '¿')) {
          //   $key = str_replace('¿', '', $sub);
          //   $next[$key] = ['null'];
          // } else {
          // }
          $key = $sub;

          if (!is_string($key)) {
            throw new \Exception("Unexpected syntax error; Did you misplaced `?` on object property? Object property must be a string, e.g. `{ key?: string }`, not: ".json_encode($key), ErrorCodes::FORBIDDEN);
          }

          $next[$key] = [];
        } else {
          $next[$key][] = $sub;
        }
      }

      // Simplify tree on last key after foreach
      if ($key && sizeof($next[$key]) === 1 && is_array($next[$key])) {
        $next[$key] = $next[$key][0];
      }

      // Indexer
      foreach ($next as $key => $value) {
        if ($key[0] !== '"') {
          if ($indexer) {
            throw new \Exception("More than one indexer property", ErrorCodes::FORBIDDEN);
          }

          $indexer = (object) [
            'key' => $key,
            'value' => $value,
          ];

          unset($next[$key]);
        }
      }

      $tree = [
        'indexer' => $indexer,
        'shape' => $next,
      ];
    } elseif (sizeof($tree) === 1 && is_array($tree[0])) {
      $tree = $tree[0];
    }
  }

  /**
   * Parses Flow annotation into AST-like tree
   *
   * @param string $annotation Flow annotation
   * @return array AST-like tree
   *
   * @see https://flow.org/en/docs/types/ - Flow Type Annotations
   */
  public static function parse(string $annotation): /*AbstractSyntaxTree*/ array {
    $annotation = preg_replace('/\/\/.*$/m', '', $annotation);
    $annotation = str_replace("\n", '', $annotation);
    $annotation = str_replace(' ', '', $annotation);
    $annotation = str_replace("\t", '', $annotation);

    $annotation = ltrim($annotation, '|&');
    $annotation = str_replace(':|', ':', $annotation);
    $annotation = str_replace(':&', ':', $annotation);
    $annotation = str_replace('[|', '[', $annotation);
    $annotation = str_replace('[&', '[', $annotation);
    $annotation = str_replace('(|', '(', $annotation);
    $annotation = str_replace('(&', '(', $annotation);

    if (preg_match('/[#@^$~]/', $annotation)) {
      throw new \Exception('Any of `#@^$~` are reserved for notations.', ErrorCodes::CONFLICT);
    }

    // Object notations:

    // Hide optionality on keys
    $annotation = str_replace('?:', '¿:', $annotation);
    // Mark keys as strings
    $annotation = preg_replace('/([\w¿]+):/', '"$1":', $annotation);

    // Simplify exact shape object notations
    $annotation = str_replace('{|', '#(', $annotation);
    $annotation = str_replacE('|}', ')', $annotation);

    // Simplify shape object notations
    $annotation = str_replace('{', '@(', $annotation);
    $annotation = str_replacE('}', ')', $annotation);

    // Replace unnamed indexers
    $annotation = preg_replace('/\[(.+)\]:/', '$1:', $annotation);

    if (preg_match('/\[.+?:.+?\]/', $annotation)) {
      // TODO: Maybe add feature later, e.g. when enumerations of strings are required
      throw new \Exception("Naming indexer is not supported", ErrorCodes::NOT_IMPLEMENTED);
    }

    // Array and tuple notations:
    $annotation = str_replace('[]', '$', $annotation);
    $annotation = str_replace('Array<', '~(', $annotation);
    $annotation = str_replace('>', ')', $annotation);
    $annotation = str_replace('[', '^(', $annotation);
    $annotation = str_replacE(']', ')', $annotation);

    // Split to words
    $words = preg_split('/([()&|?#@$^:,])/', $annotation, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

    $tree = [];

    $indexes = [];
    $index = 0;

    $pointers = [];
    $pointer =& $tree;

    while(null !== $word = array_shift($words)) {
      switch($word) {
        case '(':
          // Advance if already in progress
          if (isset($pointer[$index])) {
            $index++;
          }

          // Open new array
          $pointer[$index] = [];

          // Store refferences
          $indexes[] = $index;
          $pointers[] =& $pointer;

          // Point to the newly openned array
          $pointer =& $pointer[$index];
          $index = 0;
        break;

        case ')':
          // Restore index level up
          $index = array_pop($indexes);
          // Restore pointer
          $pointer =& $pointers[count($pointers) - 1];

          // Advance index
          $index++;
          array_pop($pointers);
        break;

        default:
          $pointer[$index] = $word;
          $index++;
        break;
      }
    }

    // echo '<pre>'.print_r($tree, true).'</pre>';
    static::walk($tree);
    // echo '<pre>'.print_r($tree, true)."</pre>";

    return $tree;
  }

  /**
   * Throw exception if validator throws unprocessed \TypeError
   *
   * Returns passed IncompatibleTypeError if validation was handled according to
   * expectations.
   *
   * @param \TypeError $th Thrown \TypeError exception
   * @return IncompatibleTypeError|void
   */
  protected static function maybeThrowException(\TypeError $th) {
    if ($th instanceof IncompatibleTypeError) {
      return $th;
    }

    throw new \Exception("Validation errors must throw IncompatibleTypeError", ErrorCodes::FORBIDDEN, $th);
  }

  /**
   * Converts special characters in the shape property into regulat property name
   *
   * @param string $key
   * @return string
   */
  protected static function shapeKeyLiteral(string $key): string {
    return trim($key, '"\'¿');
  }

  /**
   * Compiles AST-like tree into validator \Closure
   *
   * @param array $tree AST-like tree genereated with {@see Annotation::parse()}
   * @return \Closure
   */
  public static function compile(array $tree): \Closure {
    if (!isset($tree[0])) {
      throw new \Exception("Unexpected empty AST tree", ErrorCodes::CONFLICT);
    }

    $expression = static::expression($tree[0]);

    if ($expression !== Annotation::UNION_ANNOTATION) {
      array_shift($tree);
    }

    if ($expression === Annotation::TUPLE_ANNOTATION) {
      $tuple = &$tree[0];

      foreach ($tuple as &$type) {
        if (empty($type) && $type != 0) {
          throw new \Exception("Annotation syntaxt error: Unexpected `()` or missing `,` while compiling type anotation", ErrorCodes::FORBIDDEN);
        }

        if (is_array($type)) {
          $type = static::compile($type);
        } else {
          $type = Registry::get($type);
        }
      }
    } elseif ($expression === Annotation::SHAPE_ANNOTATION) {
      $shape = &$tree[0]['shape'];

      foreach ($shape as $key => &$type) {
        if (empty($type) && $type != 0) {
          throw new \Exception("Annotation syntaxt error: Unexpected `()` or missing `,` while compiling type anotation", ErrorCodes::FORBIDDEN);
        }

        if (is_array($type)) {
          $type = static::compile($type);
        } else {
          $type = Registry::get($type);
        }
      }

      $indexer = &$tree[0]['indexer'];

      if ($indexer) {
        if ($indexer->key) {
          if ($indexer->key) {
            if (is_array($indexer->key)) {
              $indexer->key = static::compile($indexer->key);
            } else {
              $indexer->key = Registry::get($indexer->key);
            }
          }
        }

        if ($indexer->value) {
          if (is_array($indexer->value)) {
            $indexer->value = static::compile($indexer->value);
          } else {
            $indexer->value = Registry::get($indexer->value);
          }
        }
      }
    } else {
      foreach ($tree as &$type) {
        if (empty($type) && $type != 0) {
          throw new \Exception("Annotation syntaxt error: Unexpected `()` or missing `,` while compiling type anotation", ErrorCodes::FORBIDDEN);
        }

        if (is_array($type)) {
          $type = static::compile($type);
        } else {
          $type = Registry::get($type);
        }
      }
    }

    switch ($expression) {
      case Annotation::UNION_ANNOTATION:
        return function($value = null) use ($tree): bool {
          $errors = [];

          foreach ($tree as $type) {
            try {
              $type($value);

              return true;
            } catch (\TypeError $th) {
              $errors[] = static::maybeThrowException($th);
            }
          }

          if (count($errors) === 1) {
            throw $errors[0];
          }

          throw new IncompatibleTypeError(
            $value,
            IncompatibleTypeError::INCOMPATIBLE_WITH_UNION,
            $errors,
          );

          return true;
        };
      break;

      case Annotation::INTERSECTION_ANNOTATION:
        return function($value = null) use ($tree): bool {
          $errors = [];

          foreach ($tree as $type) {
            try {
              $type($value);
            } catch (\TypeError $th) {
              $errors[] = static::maybeThrowException($th);
            }
          }

          if (empty($errors)) {
            return true;
          }

          throw new IncompatibleTypeError(
            $value,
            IncompatibleTypeError::INCOMPATIBLE_WITH_INTERSECTION,
            $errors
          );

          return true;
        };
      break;

      case Annotation::ARRAY_ANNOTATION:
        return function ($value) use ($tree): bool {
          if (!is_array($value)) {
            throw new IncompatibleTypeError(
              $value,
              IncompatibleTypeError::INCOMPATIBLE_WITH_ARRAY,
            );
          }

          $type = $tree[0];

          $errors = [];

          foreach ($value as $index => $item) {
            try {
              $type($item);
            } catch (\TypeError $th) {
              $errors[$index] = static::maybeThrowException($th);
            }
          }

          $errorsCount = count($errors);

          if ($errorsCount === 0) {
            return true;
          }

          throw new IncompatibleTypeError(
            $value,
            IncompatibleTypeError::INCOMPATIBLE_IN_ARRAY_MEMBERS,
            $errors,
          );

          return true;
        };
      break;

      case Annotation::SHAPE_ANNOTATION:
        $exact = &$tree[0]['exact'];
        $shape = &$tree[0]['shape'];
        $indexer = &$tree[0]['indexer'];

        return function($value) use ($exact, $shape, $indexer): bool {
          if (!is_array($value) && !is_object($value)) {
            throw new IncompatibleTypeError(
              $value,
              $exact
                ? IncompatibleTypeError::INCOMPATIBLE_WITH_EXACT_SHAPE
                : IncompatibleTypeError::INCOMPATIBLE_WITH_SHAPE,
            );
          }

          $value = (array) $value;

          $shapeKeys = array_map(function(string $key) {
            return static::shapeKeyLiteral($key);
          }, array_keys($shape));

          // Create shape without the extra annotations in the shape keys:
          $_shape = array_combine($shapeKeys, array_values($shape));

          $errors = [];

          foreach ($value as $key => $item) {
            // Force string keys
            if (!is_string($key)) {
              throw new IncompatibleTypeError(
                '\\associative array literal',
                $exact
                  ? IncompatibleTypeError::INCOMPATIBLE_WITH_EXACT_SHAPE
                  : IncompatibleTypeError::INCOMPATIBLE_WITH_SHAPE,
              );
            }

            $isExtraKey = $exact ? !array_key_exists($key, $_shape) : false;

            // Validate with indexer:
            if ($isExtraKey) { try { if ($indexer) {
                ($indexer->key)($key);
                $isExtraKey = false;
            }} catch (\TypeError $th) {
              static::maybeThrowException($th);
            }}

            if ($isExtraKey) {
              $errors[$key] = new IncompatibleTypeError(
                "\\property `${key}`",
                'missing in '.($exact ? 'exact shape' : 'shape').' but exists in object literal',
              );
            } else {
              $type = isset($_shape[$key]) ? $_shape[$key] : ($indexer ? $indexer->value : null);

              if ($type) {
                try {
                  $type($item);
                } catch (\TypeError $th) {
                  $errors[$key] = static::maybeThrowException($th);
                }
              }
            }
          }

          if ($exact) {
            $valueKeys = array_keys($value);
            $optionalKeys = array_filter(array_map(function(string $key) {
              if (strstr($key, '¿')) {
                return static::shapeKeyLiteral($key);
              }

              return null;
            }, array_keys($shape)));

            $missingKeys = Utils::missingArrayValues($valueKeys, $shapeKeys);
            $missingKeys = Utils::missingArrayValues($optionalKeys, $missingKeys);

            foreach ($missingKeys as $missingKey) {
              $errors[$missingKey] = new IncompatibleTypeError(
                "\\property `${missingKey}`",
                'missing in array literal but exists in '.($exact ? 'exact shape' : 'shape'),
              );
            }
          }

          $errorsCount = count($errors);

          if ($errorsCount === 0) {
            return true;
          }

          throw new IncompatibleTypeError(
            $value,
            $exact
              ? IncompatibleTypeError::INCOMPATIBLE_IN_EXACT_SHAPE_PROPERTIES
              : IncompatibleTypeError::INCOMPATIBLE_IN_SHAPE_PROPERTIES,
            $errors
          );

          return true;
        };
      break;

      case Annotation::TUPLE_ANNOTATION:
        $tuple = &$tree[0];
        return function ($value = null) use ($tuple): bool {
          if (!is_array($value)) {
            throw new IncompatibleTypeError(
              $value,
              IncompatibleTypeError::INCOMPATIBLE_WITH_TUPLE,
            );
          }

          $valueKeys = array_keys($value);
          $tupleKeys = array_keys($tuple);

          $valueKeysCount = count($valueKeys);
          $tupleKeysCount = count($tupleKeys);

          if ($valueKeysCount !== $tupleKeysCount) {
            throw new IncompatibleTypeError(
              "\\array literal with arity of ${valueKeysCount}",
              "incompatible with tuple type with arity of ${tupleKeysCount}",
            );
          }

          $errors = [];

          // Reindex and check
          foreach (array_values($tuple) as $index => &$type) {
            $item =& $value[$index];

            try {
              $type($item);
            } catch(\TypeError $th) {
              $errors[$index] = $th;
            }
          }

          if (empty($errors)) {
            return true;
          }

          throw new IncompatibleTypeError(
            $value,
            IncompatibleTypeError::INCOMPATIBLE_IN_TUPLE_MEMBERS,
            $errors
          );

          return true;
        };
      break;

      default:
        throw new \Exception("Not implemented expression `${expression}`", ErrorCodes::NOT_IMPLEMENTED);
      break;
    }
  }
}