<?php

namespace Duck\Types;

/**
 * Error codes constatns
 *
 * - 4XX - Errors are recoverable by changing client program
 * - 5XX - Errors usually require change of the library
 *
 */
final class ErrorCodes {

  /**
   * Refused action. This may be due to missing necessary permissions
   */
  const FORBIDDEN = 403;

  /**
   * The requested resource could not be found
   */
  const NOT_FOUND = 404;

  /**
   * Not allowed method
   */
  const METHOD_NOT_ALLOWED = 405;

  /**
   * Unable to be processed because of conflict in the current state
   */
  const CONFLICT = 409;

  /**
   * A generic error message, given when an unexpected condition was
   * encountered and no more specific message is suitable
   */
  const INTERNAL = 500;

  /**
   * Unable to be fulfiled. Usually this implies future availability
   */
  const NOT_IMPLEMENTED = 501;
}