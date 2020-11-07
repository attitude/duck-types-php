# Duck\Types\ErrorCodes
Error codes constants

- 4XX - Errors are recoverable by changing client program
- 5XX - Errors usually require change of the library




## Constants

| Name | Description |
|------|-------------|
|ErrorCodes::FORBIDDEN = 403|Refused action. This may be due to missing necessary permissions|
|ErrorCodes::NOT_FOUND = 404|The requested resource could not be found|
|ErrorCodes::METHOD_NOT_ALLOWED = 405|Not allowed method|
|ErrorCodes::CONFLICT = 409|Unable to be processed because of conflict in the current state|
|ErrorCodes::INTERNAL = 500|A generic error message, given when an unexpected condition was
encountered and no more specific message is suitable|
|ErrorCodes::NOT_IMPLEMENTED = 501|Unable to be fulfiled. Usually this implies future availability|



---

