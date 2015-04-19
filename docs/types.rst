Properties & types
==================

Each property in your Domain Object should be an instance of the appropriate Type class. Pheasant uses the type to
determine a few things:
 * The database column type (e.g. `INT`, `VARCHAR`)
 * How do convert the database value to PHP and vice versa (e.g. a `DATETIME` column returns a PHP DateTime
   object instead of a string)

Pheasant ships with a dozen of built-in types. If the built-in types don't to the trick, you can add your own
types by subclassing `\Pheasant\Types\AbstractType`. See the built-in types for inspiration.


Built-in types
--------------

Most of the built-in types have the same name of their corresponding column type in MySQL:

 * BigInteger
 * Boolean
 * Character
 * DateTime
 * Decimal
 * Integer
 * Sequence
 * Set
 * SmallInteger
 * String
 * UnixTimestamp


Type arguments
--------------

Each type takes one or more arguments to set some details. For example, the `Integer` type takes the int-length as the
first parameter. The second argument must be a \Pheasant\Option instance, which you can define by creating an instance
of that class, or you can define it as a string.

At the moment, the docs on which type takes which arguments is not up to date. Please refer to the tests (particulary
`TypesTest.php` and `Examples/*`) for examples and inspiration.


Defining properties and types in your DomainObject
--------------------------------------------------

See :doc:`domainobjects` for more details and examples on how to define properties and their types.
