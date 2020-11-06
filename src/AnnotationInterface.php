<?php

namespace Duck\Types;

interface AnnotationInterface {
  public static function compile(array $tree): \Closure;
  public static function parse(string $annotation): /*AbstractSyntaxTree*/ array;
}
