Changes between Pheasant 1.1.2 and 1.1.3
========================================

  - Support for MySQL aggregate functions in `Collections` 409c892 0161ec7

```php
<?php
  
  $sum = Llama::all()->sum('age'); // return the SUM of all llama ages
```

  - Database deadlocks now throw a typed `DeadlockException` 04ee71a
  - Magic finders support backtick quoted column names 10367ac 90e0e31 b5436b9
  - Domain object enumeration skips abstract classes 64f441f
