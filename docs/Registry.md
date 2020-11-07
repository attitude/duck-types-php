# Duck\Types\Registry


## Implements:
Duck\Types\RegistryInterface




## Methods

| Name | Description |
|------|-------------|
|[Registry::exists()](#registryexists)|Checks if type exists in registry|
|[Registry::get()](#registryget)|Retrieves an already registered type by it's name, literal validators|
|[Registry::set()](#registryset)|Registers a new type|


---

---

### Registry::exists()

**Description**


```php
public static exists (string $name): bool
```

Checks if type exists in registry

**Parameters**

* `(string) $name`
: Type name to check

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

* `(string) $name`
: Type name alias or type annotation to retireve

**Return Values**

`\Closure`




---

### Registry::set()

**Description**


```php
public static set (string $name, callable|string $type): \Closure
```

Registers a new type

**Parameters**

* `(string) $name`
: New type name (alias) to register that can be later  
retrieved by calling {@see \self::get()}.* `(callable|string) $type`
: Validation \Closure, callable to  
{@see \self::wrap()}, type name alias or  
Flow annotation.

**Return Values**

`\Closure`




