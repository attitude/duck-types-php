# Duck\Types\IncompatibleTypeError
Incompatible type error class

Extends default 'TypeError class. Throw this in custom validation \Closure
validators.

This class is final. Use composition instead of inheritance.
## Implements:
Throwable

## Extend:

TypeError

## Constants

| Name | Description |
|------|-------------|
|IncompatibleTypeError::INCOMPATIBLE_WITH_UNION = ":incompatible-with-union:"||
|IncompatibleTypeError::INCOMPATIBLE_WITH_INTERSECTION = ":incompatible-with-intersection:"||
|IncompatibleTypeError::INCOMPATIBLE_WITH_SHAPE = ":incompatible-with-shape:"||
|IncompatibleTypeError::INCOMPATIBLE_WITH_EXACT_SHAPE = ":incompatible-with-exact-shape:"||
|IncompatibleTypeError::INCOMPATIBLE_IN_SHAPE_PROPERTIES = ":incompatible-in-shape-properties:"||
|IncompatibleTypeError::INCOMPATIBLE_IN_EXACT_SHAPE_PROPERTIES = ":incompatible-in-exact-shape-properties:"||
|IncompatibleTypeError::INCOMPATIBLE_WITH_ARRAY = ":incompatible-with-array:"||
|IncompatibleTypeError::INCOMPATIBLE_IN_ARRAY_MEMBERS = ":incompatible-in-array-mebers:"||
|IncompatibleTypeError::INCOMPATIBLE_WITH_TUPLE = ":incompatible-with-tuple:"||
|IncompatibleTypeError::INCOMPATIBLE_IN_TUPLE_MEMBERS = ":incompatible-in-tuple-members:"||

## Methods

| Name | Description |
|------|-------------|
|[IncompatibleTypeError->debug()](#incompatibletypeerrordebug)|Helper method to introspect the caught errors tree|
|[IncompatibleTypeError::escapedGettype()](#incompatibletypeerrorescapedgettype)|Returns variable type except strings with "\\" escape character|
|[IncompatibleTypeError::flatArray()](#incompatibletypeerrorflatarray)|Flattens passed array into one-dimmensional array|
|[IncompatibleTypeError->getMessages()](#incompatibletypeerrorgetmessages)|Retrieves all error messages|
|[IncompatibleTypeError->getUnexpected()](#incompatibletypeerrorgetunexpected)|Returns unexpected condition met|
|[IncompatibleTypeError::getttype()](#incompatibletypeerrorgetttype)|Returns type representation of the variable|

## Inherited methods

| Name | Description |
|------|-------------|
| [IncompatibleTypeError->__construct](https://secure.php.net/manual/en/error.__construct.php) | Construct the error object |
| [IncompatibleTypeError->__toString](https://secure.php.net/manual/en/error.__tostring.php) | String representation of the error |
| [IncompatibleTypeError->__wakeup](https://secure.php.net/manual/en/error.__wakeup.php) | - |
| [IncompatibleTypeError->getCode](https://secure.php.net/manual/en/error.getcode.php) | Gets the error code |
| [IncompatibleTypeError->getFile](https://secure.php.net/manual/en/error.getfile.php) | Gets the file in which the error occurred |
| [IncompatibleTypeError->getLine](https://secure.php.net/manual/en/error.getline.php) | Gets the line in which the error occurred |
| [IncompatibleTypeError->getMessage](https://secure.php.net/manual/en/error.getmessage.php) | Gets the error message |
| [IncompatibleTypeError->getPrevious](https://secure.php.net/manual/en/error.getprevious.php) | Returns previous Throwable |
| [IncompatibleTypeError->getTrace](https://secure.php.net/manual/en/error.gettrace.php) | Gets the stack trace |
| [IncompatibleTypeError->getTraceAsString](https://secure.php.net/manual/en/error.gettraceasstring.php) | Gets the stack trace as a string |

---

---

### IncompatibleTypeError->debug()

**Description**


```php
public debug (void): void
```

Helper method to introspect the caught errors tree

**Parameters**

`This function has no parameters.`


**Return Values**

`void`




---

### IncompatibleTypeError::escapedGettype()

**Description**


```php
public static escapedGettype (mixed $given): string
```

Returns variable type except strings with "\\" escape character

**Parameters**

* `(mixed) $given`


**Return Values**

`string`




---

### IncompatibleTypeError::flatArray()

**Description**


```php
public static flatArray (array $array): array
```

Flattens passed array into one-dimmensional array

**Parameters**

* `(array) $array`


**Return Values**

`array`




---

### IncompatibleTypeError->getMessages()

**Description**


```php
public getMessages (void): string[]|string
```

Retrieves all error messages

**Parameters**

`This function has no parameters.`


**Return Values**

`string[]|string`




---

### IncompatibleTypeError->getUnexpected()

**Description**


```php
public getUnexpected (void): string
```

Returns unexpected condition met

**Parameters**

`This function has no parameters.`


**Return Values**

`string`




---

### IncompatibleTypeError::getttype()

**Description**


```php
public static getttype (mixed $value): string
```

Returns type representation of the variable

**Parameters**

* `(mixed) $value`


**Return Values**

`string`




