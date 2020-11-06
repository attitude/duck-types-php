<?php

namespace Duck\Types;

interface TypeInterface {
  public static function pass($value = null, $type, $default = null);
  public static function get(string $name): \Closure;
  public static function set(string $name, $type): \Closure;
  public static function wrap(callable $function, string $type = null, callable $shouldThrow = null): \Closure;
  public static function shouldThrowIncompatibleTypeError(string $message, callable $test);
}
