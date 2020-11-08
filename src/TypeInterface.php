<?php

namespace Duck\Types;

interface TypeInterface {
  public static function for(string $name, $type = null): \Closure;
  public static function pass($value = null, $type, $default = null);
  public static function is(string $type, $value = null): bool;
  public static function all(\Closure ...$conditions): \Closure;
  public static function any(\Closure ...$conditions): \Closure;
  public static function wrap(callable $function, string $type = null, callable $shouldThrow = null): \Closure;
  public static function shouldThrow(string $message, callable $test);
}
