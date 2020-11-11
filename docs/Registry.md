# Duck\Types\Registry


## Implements:
Duck\Types\RegistryInterface




## Methods

| Name | Description |
|------|-------------|
|[Registry::exists()](#registryexists)|Checks if type exists in registry & tries to load it if not|
|[Registry::get()](#registryget)|Retrieves an already registered type by it's name, literal validators|
|[Registry::has()](#registryhas)|Checks if the type is already registered|
|[Registry::registerAutoloader()](#registryregisterautoloader)|Registers a type-autoloader method to resolve loading of type.|
|[Registry::set()](#registryset)|Registers a new type|


---

---

### Registry::exists()

**Description**


```php
public static exists (string $name): bool
```

Checks if type exists in registry & tries to load it if not

Uses autoload registerd using `Registry::registerAutoloader()`.

**IMPORTANT:** Do not call this method inside any type-autoload function.
If yu need to check if the type was already registered, use `Registry::has()`

**Parameters**

* `(string) $name`: Type name to check


**Return Values**

`bool`




---

### Registry::get()

**Description**


```php
public static get (string $name): \Closure
```

Retrieves an already registered type by it's name, literal validators



**Parameters**

* `(string) $name`: Type name alias or type annotation to retireve


**Return Values**

`\Closure`




---

### Registry::has()

**Description**


```php
public static has (string $name): bool
```

Checks if the type is already registered

Difference between `Registry::exists()` is that this method escapess
all autoloader, even the built-in.

**Parameters**

* `(string) $name`


**Return Values**

`bool`




---

### Registry::registerAutoloader()

**Description**


```php
public static registerAutoloader (string $name, \Closure $resolver): void
```

Registers a type-autoloader method to resolve loading of type.

Autoload function should try to set the requested type by calling
`Type:for($name, $annotationOrClosure)` inside the type-autoload function.

**Parameters**

* `(string) $name`: Autoloader anme to register
* `(\Closure) $resolver`: Callable that is called when trying to find out whether the type might exist. Callable should expect one paramenter, the name of the type.


**Return Values**

`void`




---

### Registry::set()

**Description**


```php
public static set (string $name, callable|string $type): \Closure
```

Registers a new type



**Parameters**

* `(string) $name`: New type name (alias) to register that can be later retrieved by calling [self::get()](#registryget).
* `(callable|string) $type`: Validation \Closure, callable to [self::wrap()](#registryget), type name alias or Flow annotation.


**Return Values**

`\Closure`




