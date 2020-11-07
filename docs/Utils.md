# Duck\Types\Utils







## Methods

| Name | Description |
|------|-------------|
|[Utils::assertCallable()](#utilsassertcallable)|Asserts valid type|
|[Utils::const()](#utilsconst)|Retrieves constant by it's name|
|[Utils::getReturnType()](#utilsgetreturntype)|Retrieves return value of any callable|
|[Utils::missingArrayValues()](#utilsmissingarrayvalues)||


---

---

### Utils::assertCallable()

**Description**


```php
public static assertCallable (callable $callable, string $name): void
```

Asserts valid type

**Parameters**

* `(callable) $callable`
* `(string) $name`


**Return Values**

`void`




---

### Utils::const()

**Description**


```php
public static const (string $name, mixed $default): mixed
```

Retrieves constant by it's name
If constant is not defained, default value is returned if provided.
**Parameters**

* `(string) $name`
: Constant name* `(mixed) $default`
: Default value

**Return Values**

`mixed`




---

### Utils::getReturnType()

**Description**


```php
public static getReturnType (callable $function): string|void
```

Retrieves return value of any callable

**Parameters**

* `(callable) $function`


**Return Values**

`string|void`




---

### Utils::missingArrayValues()

**Description**


```php
 missingArrayValues (void): void
```



**Parameters**

`This function has no parameters.`


**Return Values**

`void`


