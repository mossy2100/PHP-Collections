# PHP Key-Value Storage Comparison

A comprehensive comparison of different key-value storage mechanisms in PHP.

## Quick Reference Table

| Feature | Dictionary | PHP Array | WeakMap | SplObjectStorage |
|---------|-----------|-----------|---------|------------------|
| **Key Types** | Any type | `int\|string` only | Objects only | Objects only |
| **Key Type Safety** | Runtime validated | None | Built-in | Built-in |
| **Value Types** | Any type | Any type | Any type | Any type |
| **Value Type Safety** | Runtime validated | None | None | None |
| **Object Key Support** | ✅ Yes | ❌ No | ✅ Yes | ✅ Yes |
| **Array Key Support** | ✅ Yes | ❌ No | ❌ No | ❌ No |
| **Resource Key Support** | ✅ Yes | ❌ No | ❌ No | ❌ No |
| **Reference Type** | Strong | Strong | Weak | Strong |
| **Memory Management** | Manual | Manual | Automatic GC | Manual |
| **Prevents Memory Leaks** | ❌ No | ❌ No | ✅ Yes | ❌ No |
| **Iteration** | ✅ Yes | ✅ Yes | ✅ Yes | ✅ Yes |
| **ArrayAccess** | ✅ Yes | Native | ❌ No | ✅ Yes |
| **Countable** | ✅ Yes | Native | ✅ Yes | ✅ Yes |
| **Serializable** | ⚠️ Partial | ✅ Yes | ❌ No | ⚠️ Partial |
| **PHP Version** | 8.3+ | All | 8.0+ | 5.1+ |
| **Performance** | Good (O(1)) | Excellent (O(1)) | Good (O(1)) | Good (O(1)) |

## Detailed Comparison

### Key Types

#### Dictionary (Galaxon\Collections)
```php
// Supports ANY PHP type as keys
$dict = new Dictionary('mixed', 'mixed');

$dict['string'] = 'value';              // ✅ String keys
$dict[42] = 'value';                    // ✅ Integer keys
$dict[new DateTime()] = 'value';        // ✅ Object keys
$dict[[1, 2, 3]] = 'value';            // ✅ Array keys
$dict[fopen('file.txt', 'r')] = 'val'; // ✅ Resource keys
$dict[true] = 'value';                 // ✅ Boolean keys
$dict[null] = 'value';                 // ✅ Null key
```

#### PHP Array
```php
// Only string or integer keys (auto-converts)
$arr = [];

$arr['string'] = 'value';  // ✅ String keys
$arr[42] = 'value';        // ✅ Integer keys
$arr[true] = 'value';      // ⚠️ Converts to 1
$arr[null] = 'value';      // ⚠️ Converts to ''
$arr[1.5] = 'value';       // ⚠️ Converts to 1

// Objects/arrays as keys not supported
$arr[new DateTime()] = 'value';  // ❌ Fatal error
$arr[[1, 2]] = 'value';          // ❌ Fatal error
```

#### WeakMap
```php
// Only object keys
$map = new WeakMap();

$obj = new DateTime();
$map[$obj] = 'value';      // ✅ Object keys only

$map['string'] = 'value';  // ❌ TypeError
$map[42] = 'value';        // ❌ TypeError
```

#### SplObjectStorage
```php
// Only object keys
$storage = new SplObjectStorage();

$obj = new DateTime();
$storage[$obj] = 'value';      // ✅ Object keys only
$storage->attach($obj, 'val'); // ✅ Alternative syntax

$storage['string'] = 'value';  // ❌ TypeError
```

---

### Type Safety

#### Dictionary
```php
// Runtime type validation for both keys and values
$dict = new Dictionary('string', 'int');

$dict['name'] = 42;        // ✅ Valid
$dict['age'] = 'invalid';  // ❌ TypeError
$dict[123] = 42;           // ❌ TypeError (key must be string)
```

#### PHP Array
```php
// No type safety - accepts anything
$arr = [];
$arr['name'] = 'Alice';
$arr['age'] = 42;
$arr['data'] = [1, 2, 3];  // No validation
```

#### WeakMap
```php
// Keys must be objects, values can be anything
$map = new WeakMap();
$map[$obj] = 'string';     // ✅
$map[$obj] = 42;           // ✅ No value type checking
```

#### SplObjectStorage
```php
// Keys must be objects, values can be anything
$storage = new SplObjectStorage();
$storage[$obj] = 'string'; // ✅
$storage[$obj] = 42;       // ✅ No value type checking
```

---

### Reference Behavior

#### Strong References (Dictionary, Array, SplObjectStorage)
```php
$dict = new Dictionary();
$obj = new DateTime();
$dict[$obj] = 'value';

unset($obj);  // Object still exists (referenced by $dict)
// Memory not freed until $dict is destroyed
```

#### Weak References (WeakMap)
```php
$map = new WeakMap();
$obj = new DateTime();
$map[$obj] = 'value';

unset($obj);  // Object is destroyed (weak reference)
// Entry automatically removed from WeakMap
// Memory freed immediately by garbage collector
```

**Use Case for WeakMap:**
```php
// Caching without memory leaks
class Cache {
    private WeakMap $cache;
    
    public function __construct() {
        $this->cache = new WeakMap();
    }
    
    public function get(object $obj): mixed {
        return $this->cache[$obj] ?? null;
    }
    
    public function set(object $obj, mixed $value): void {
        $this->cache[$obj] = $value;
    }
}

// When objects are destroyed, cache entries auto-cleanup ✅
```

---

### Key Uniqueness

#### Dictionary & PHP Array (By Value)
```php
$dict = new Dictionary();
$obj1 = new stdClass();
$obj2 = new stdClass();

$dict[$obj1] = 'first';
$dict[$obj2] = 'second';

// Both stored - different values (even if identical structure)
echo $dict->count();  // 2
```

#### WeakMap & SplObjectStorage (By Identity)
```php
$map = new WeakMap();
$obj1 = new stdClass();
$obj2 = $obj1;  // Same object

$map[$obj1] = 'first';
$map[$obj2] = 'second';  // Overwrites 'first'

echo $map[$obj1];  // 'second' (same object)
```

---

### Iteration

#### Dictionary
```php
$dict = new Dictionary();
$dict['a'] = 1;
$dict['b'] = 2;

foreach ($dict as $key => $value) {
    echo "$key => $value\n";
}
// Preserves insertion order
```

#### PHP Array
```php
$arr = ['a' => 1, 'b' => 2];

foreach ($arr as $key => $value) {
    echo "$key => $value\n";
}
// Preserves insertion order
```

#### WeakMap
```php
$map = new WeakMap();
$obj1 = new stdClass();
$obj2 = new stdClass();
$map[$obj1] = 1;
$map[$obj2] = 2;

foreach ($map as $obj => $value) {
    echo get_class($obj) . " => $value\n";
}
// No guaranteed order
```

#### SplObjectStorage
```php
$storage = new SplObjectStorage();
$obj1 = new stdClass();
$obj2 = new stdClass();
$storage[$obj1] = 1;
$storage[$obj2] = 2;

foreach ($storage as $obj) {
    echo get_class($obj) . " => " . $storage[$obj] . "\n";
}
// Preserves insertion order
```

---

### ArrayAccess Syntax

#### Dictionary
```php
$dict = new Dictionary();
$dict['key'] = 'value';      // ✅ ArrayAccess
echo $dict['key'];            // ✅
isset($dict['key']);          // ✅
unset($dict['key']);          // ✅
```

#### PHP Array
```php
$arr = [];
$arr['key'] = 'value';        // ✅ Native
// All array operations supported
```

#### WeakMap
```php
$map = new WeakMap();
$obj = new stdClass();
$map[$obj] = 'value';         // ✅ ArrayAccess
echo $map[$obj];              // ✅
isset($map[$obj]);            // ✅
unset($map[$obj]);            // ✅
```

#### SplObjectStorage
```php
$storage = new SplObjectStorage();
$obj = new stdClass();
$storage[$obj] = 'value';     // ✅ ArrayAccess
echo $storage[$obj];          // ✅
isset($storage[$obj]);        // ✅
unset($storage[$obj]);        // ✅

// Also supports attach/detach API
$storage->attach($obj, 'value');
$storage->detach($obj);
```

---

### Serialization

#### Dictionary
```php
$dict = new Dictionary();
$dict['key'] = 'value';

$serialized = serialize($dict);  // ⚠️ Works for simple types
// Object/resource keys may cause issues
```

#### PHP Array
```php
$arr = ['key' => 'value'];
$serialized = serialize($arr);   // ✅ Full support
$restored = unserialize($serialized);
```

#### WeakMap
```php
$map = new WeakMap();
$serialized = serialize($map);   // ❌ Exception
// Cannot serialize weak references
```

#### SplObjectStorage
```php
$storage = new SplObjectStorage();
$serialized = serialize($storage);  // ⚠️ Partial support
// Objects must be serializable
```

---

## Use Case Recommendations

### Use **Dictionary** when:
- ✅ You need type safety for keys and values
- ✅ You want to use objects, arrays, or resources as keys
- ✅ You need comprehensive type validation
- ✅ You're building type-safe data structures
- ✅ You want a clean, modern API

### Use **PHP Array** when:
- ✅ You only need string/int keys
- ✅ Performance is critical
- ✅ You need native PHP integration
- ✅ Serialization is important
- ✅ Simple key-value storage is sufficient

### Use **WeakMap** when:
- ✅ You're implementing caches or memoization
- ✅ You want automatic memory cleanup
- ✅ You need to associate data with objects without preventing GC
- ✅ Memory leaks are a concern
- ✅ Keys are objects and can be garbage collected

### Use **SplObjectStorage** when:
- ✅ You need a set of objects with associated data
- ✅ You want object identity-based storage
- ✅ You need the attach/detach API
- ✅ You're working with object collections
- ✅ PHP 5.x compatibility is needed

---

## Performance Comparison

All four structures provide **O(1)** average-case complexity for basic operations:

| Operation | Dictionary | PHP Array | WeakMap | SplObjectStorage |
|-----------|-----------|-----------|---------|------------------|
| **Insert** | O(1) | O(1) | O(1) | O(1) |
| **Lookup** | O(1) | O(1) | O(1) | O(1) |
| **Delete** | O(1) | O(1) | O(1) | O(1) |
| **Iteration** | O(n) | O(n) | O(n) | O(n) |

**Notes:**
- PHP arrays are the fastest (native C implementation)
- Dictionary has overhead from type validation
- WeakMap has GC overhead but prevents memory leaks
- SplObjectStorage has dual API overhead

---

## Memory Considerations

### Dictionary & PHP Array & SplObjectStorage
```php
// Strong references - manual memory management required
$dict = new Dictionary();
for ($i = 0; $i < 10000; $i++) {
    $obj = new HeavyObject($i);
    $dict[$obj] = $i;
}
// All 10,000 objects stay in memory until $dict is destroyed
```

### WeakMap
```php
// Weak references - automatic memory management
$map = new WeakMap();
for ($i = 0; $i < 10000; $i++) {
    $obj = new HeavyObject($i);
    $map[$obj] = $i;
    // $obj goes out of scope and is GC'd immediately
}
// No memory leak! Entries auto-removed as objects are destroyed
```

---

## Code Examples

### Dictionary - Type-Safe Multi-Type Keys
```php
use Galaxon\Collections\Dictionary;

$config = new Dictionary('string', 'mixed');
$config['database'] = ['host' => 'localhost', 'port' => 3306];
$config['timeout'] = 30;

// Object as key
$userPrefs = new Dictionary('object', 'array');
$user = new User('alice');
$userPrefs[$user] = ['theme' => 'dark', 'lang' => 'en'];
```

### PHP Array - General Purpose
```php
$data = [
    'name' => 'Alice',
    'age' => 30,
    'roles' => ['admin', 'user']
];

// Fast, flexible, native
```

### WeakMap - Automatic Cache
```php
class ImageCache {
    private WeakMap $cache;
    
    public function __construct() {
        $this->cache = new WeakMap();
    }
    
    public function getThumbnail(Image $img): string {
        if (!isset($this->cache[$img])) {
            $this->cache[$img] = $this->generateThumbnail($img);
        }
        return $this->cache[$img];
    }
    
    // When Image objects are destroyed, cache auto-cleans
}
```

### SplObjectStorage - Object Set with Data
```php
$observers = new SplObjectStorage();

$obs1 = new Observer('logger');
$obs2 = new Observer('emailer');

$observers->attach($obs1, ['priority' => 10]);
$observers->attach($obs2, ['priority' => 5]);

foreach ($observers as $observer) {
    $data = $observers[$observer];
    echo "Priority: " . $data['priority'] . "\n";
}
```

---

## Summary

| Choose | If You Need |
|--------|-------------|
| **Dictionary** | Type safety + flexible keys (any type) |
| **PHP Array** | Performance + native integration |
| **WeakMap** | Automatic memory management for object keys |
| **SplObjectStorage** | Object collections with metadata |

Each structure has its place in modern PHP development. Choose based on your specific requirements for type safety, key types, memory management, and performance needs.
