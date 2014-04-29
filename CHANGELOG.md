Changes between Pheasant 1.2.0 and 1.2.1
========================================

  - Fixed a regression whereby `Collection::order()` was no longer chainable.

Changes between Pheasant 1.1.2 and 1.2.0
========================================

  - Support for MySQL aggregate functions in `Collections` 409c892 0161ec7

```php
<?php

  $sum = Llama::all()->sum('age'); // return the SUM of all llama ages
```

  - Database deadlocks now throw a typed `DeadlockException` 04ee71a
  - Magic finders support backtick quoted column names 10367ac 90e0e31 b5436b9
  - Domain object enumeration skips abstract classes 64f441f
  - DomainObject::load now supports a whitelist 5219c10
  - Identities are now comparable b38ede4
  - DomainObject::lock now takes a closure for when the object has changed e07ad64
