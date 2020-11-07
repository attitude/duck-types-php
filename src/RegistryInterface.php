<?php

namespace Duck\Types;

interface RegistryInterface {
  public static function exists(string $name): bool;
  public static function set(string $name, \Closure $type): \Closure;
  public static function get(string $name): \Closure;
}
