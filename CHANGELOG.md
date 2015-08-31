Changes between Pheasant 1.3.2 and 2.0.0
========================================

  - We've made Pheasant compatible with PHP7. Unfortunately, PHP7 introduces a couple of new reserved classnames
    (including String). We've added a `Type` suffix to all the bundled types to resolve that. So `\Pheasant\Types\String`
    is now `\Pheasant\Types\StringType`. (#148)

Changes between Pheasant 1.3.1 and 1.3.2
========================================

  - Fixed an issue which caused queries to fail when using special table names. Now they are backticked (#147, #144) 
  - In relationships, when throwing exceptions: be a bit more verbose and tell which localValue could not be found (#141)
  - Remove executable bit on files (#140)
  - Fixed an issue which caused IN() queries to fail when empty (#134)
  - Fixed an issue which caused DomainObject methods not to be accessible when using includes (#133)
  - Fixed an issue which caused cached objects to be hydrated again (#132)

Changes between Pheasant 1.3.0 and 1.3.1
========================================

  - Bug fixes

Changes between Pheasant 1.2.1 and 1.3.0
========================================

  - Documentation fixes (#58) c53a4a6
  - Fixed decimal marshalling in specific locales (#103) e0f78a1
  - Connections can be overridden on a per-class basis (#104) c05883d
  - Distinguish NULL from empty string (#106) 790dcd1
  - Connection respects PHEASANT_DEBUG env 303fe4c
  - SQL binding improvements e0f78a1

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
