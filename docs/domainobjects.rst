Domain Objects
==============

The domain object represents an object that can be persisted in Pheasant.


Basic example
-------------

.. code-block:: php

    <?php

    use \Pheasant;
    use \Pheasant\Types;

    class Post extends DomainObject
    {
        public function properties()
        {
            return array(
                'postid'    => new Types\Integer(11, 'primary auto_increment'),
                'title'     => new Types\String(255, 'required'),
                'type'      => new Types\String(128, 'default=blog'),
                'timestamp' => new Types\DateTime(),
                'authorid'  => new Types\Integer(11)
            );
        }
    }


Defining properties
-------------------

Each DomainObject should at least define a `properties()` method. In the `properties()` you set
which columns you have in your database table, and what type they are. There are built-in types
for the most used MySQL column types.

See doc:`types` for more details.


Initialization
--------------

A domain object has a `Schema` instance that defines everything about it including:

 * Properties
 * Relationships
 * Custom getter/setters
 * Events

The top-level `Pheasant` instance stores a mapping of class names to `Schema` instances. Whenever
a domain object is instantiated or a static-method is called on one, the schema instance is checked.
If one doesn't exist, it's initialized.

The initialization happens in `DomainObject::initialize`, for which the default implementation is to
look for several template methods in the object.

properties()
    A map of column names to Type objects (see `types`)

relationships()
    A map of keys to RelationType objects representing 1-n or 1-1 relationships  (see `relationships`)

tableName()
    The database table name to map to, defaults to the name of the class

mapper()
    The mapper instance to use, defaults to the `mapping/rowmapper`.

Property Access
---------------

Once properties have been defined in an objects schema, they are available via property access for read and write.

.. code-block:: php

    <?php

    $post = new Post();
    $post->title = 'Test Post';
    $post->save();

    echo $post->title; // shows 'Test Post'

    $post->title = 'Updated Title';
    $post->save();

.. note::

    You can see which properties have been changed on a domain object in-between saves using the `changes()` method.

    Calling save on an unchanged object won't do anything.


Identity
--------

A domain object has some sort of primary key. This is exposed within the domain object as an `Identity`. This object
can be easily converted into a `Criteria` object for locating the object.

Any property that is either a `Sequence` or is defined with the `primary` option is considered part of the `Identity`. Composite keys
are supported.


Constructors
------------

The default constructor for a domain object allows for an array of key/values to be passed in:

.. code-block:: php

    <?php

    $post = new Post(array('title'=>'Test Post'));
    $post->save();

If you want to have a different constructor for your domain object, you must override the `construct()` method, as
the actual `__construct()` method is final to ensure it's always available.

.. code-block:: php

    <?php

    class Post extends DomainObject
    {
        public function construct($title)
        {
          $this->set('title', $title);
        }
    }

    $post = new Post('Test Post');
    echo $post->title; // shows 'Test Post'


Inheritance
-----------

Inheritance and extending domain objects isn't something that has any explicit support, although it would certainly
be possible to override the `properties` method and extend it.


Defining events
---------------

Code can be triggered before and after create, update and delete operations.

.. code-block:: php

    <?php

    use \Pheasant;
    use \Pheasant\Events;
    use \Pheasant\Types;

    class Post extends DomainObject
    {
      public function properties()
      {
        return array(
          'postid'      => new Types\Sequence(),
          'title'       => new Types\String(255),
          'timecreated' => new Types\Integer(11),
          ));
      }

      public function beforeCreate($post)
      {
        $d->timecreated = time();
      }
    }

Optionally, domain objects provide the following implicit hooks which can be overriden:

- `afterCreate`
- `beforeUpdate`, `afterUpdate`


Transactions
------------

Transactions can be created globally:

.. code-block:: php

    <?php


    \Pheasant::transaction(function() {
      $post = new Post(array('title'=>'First Post!'));
      $post->save();
    });

Or transactions can be invoked on an instance:

.. code-block:: php

    <?php

    $post = new Post(array('title'=>'First Post!'));

    $post->transaction(function($obj) {
      $obj->save();
    });
