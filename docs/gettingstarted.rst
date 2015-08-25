
Getting Started
===============

This document will get you up and running with a basic Pheasant environment. You'll need:

 * MySQL 5.1+
 * PHP 5.3.1+
 * `Composer`_

.. _Composer: http://getcomposer.org/


Installing
----------

The easiest way to install Pheasant in your project is to use `Composer`_ and include the following in your `composer.json` file:

.. code-block:: json

    {
        "require": {
            "lox/pheasant": "1.1.*"
        }
    }

After adding pheasant as a dependency of your project, update with `composer update` and you'll have a working pheasant environment.

See `Packagist <http://packagist.org/packages/lox/pheasant>`_ for more details.


Loading and Connecting
----------------------

Pheasant uses the class loading mechanism in `Composer`_, which is basically a `PSR-0` loader.

.. code-block:: php

    <?php

    // use composer's autoload mechanism
    require_once('vendor/autoload.php');

    // setup database connections
    Pheasant::setup('mysql://user:pass@localhost:3306/mydb');


Defining Objects
----------------

Anything mapped to the database is referred to as a `Domain Object` in Pheasant. The easiest way
to define properties on a domain object is to simply extend `\Pheasant\DomainObject` and implement
a `properties()` method.

In the absence of an explicit definition, the tablename is inferred from the classname: `post`.

.. code-block:: php

    <?php

    use \Pheasant\Types;

    class Post extends \Pheasant\DomainObject
    {
      public function properties()
      {
        return array(
          'postid'    => new Types\IntegerType(11, 'primary auto_increment'),
          'title'     => new Types\StringType(255, 'required'),
          'type'      => new Types\StringType(128, 'default=blog'),
          'timestamp' => new Types\DateTimeType(),
        );
      }
    }

Properties are mapped to the same named column in the database. See :doc:`mapping/types` for more about what types are available and their parameters.

Creating Tables
---------------

Pheasant tends to stay out of the way when it comes to creating and altering tables, but it does provide a minimal helper for the creation of tables:

.. code-block:: php

    <?php

    $migrator = new \Pheasant\Migrate\Migrator();
    $migrator->create('post', Post::schema());

Alternately, DomainObjects provide a tableName() method that returns the table name as a string.


Saving and Updating
-------------------

Whilst Pheasant uses the data mapper pattern, for convenience domain objects have activerecord-like helpers:

.. code-block:: php

    <?php

    $post = new Post();
    $post->title = "The joys of llama farming";
    $post->timestamp = new \DateTime('2013-01-01');
    $post->save();

Simple as that. Subsequent changes will update the record with whatever columns have been changed.

Finding
-------

The core of Pheasant's finder capability is based around `find()` and `one()`. Find returns a `Collection`, where
one returns a single object.

See :doc:`finding` for more details.

.. code-block:: php

    <?php

    // by identifier
    $post = Post::byId(1);

    // using a magic finder method
    $posts = Post::findByTitleAndTimestamp('The joys of llama farming', '2013-01-01');
    $posts = Post::findByType(array('blog', 'article'));

    // by insertion order
    $post = Post::find()->latest();

    // paged, 1 - 100
    $post = Post::find()->limit(1,100);


If you prefer direct sql, that works too and correctly handles binding `null` and `array` parameter. Note that `?` is used
for variable interpolation:

.. code-block:: php

    <?php

    // using SQL interpolation
    $post = Post::find('title LIKE ?', '%llama%');

    // automatic IN binding
    $posts = Post::find('type IN ?', array('blog', 'article'));


Relationships
-------------

An object defines what objects relate to it in the `relationships()` method.

See :doc:`relationships` for more details.

.. code-block:: php

    <?php

    use \Pheasant;
    use \Pheasant\Types;

    class Post extends DomainObject
    {
        public function properties()
        {
            return array(
                'postid'    => new Types\IntegerType(11, 'primary auto_increment'),
                'title'     => new Types\StringType(255, 'required'),
                'type'      => new Types\StringType(128, 'default=blog'),
                'timestamp' => new Types\DateTimeType(),
                'authorid'  => new Types\IntegerType(11)
            );
        }

        public function relationships()
        {
            return array(
                'Author' => Author::hasOne('authorid');
                );
        }
    }

    class Author extends DomainObject
    {
        public function properties()
        {
            return array(
                'authorid' => new Types\IntegerType(11, 'primary auto_increment'),
                'fullname' => new Types\StringType(255, 'required')
                );
        }

        public function relationships()
        {
            return array(
                'Posts' => Post::hasOne('authorid')
                );
        }
    }

    // create some objects
    $author = new Author(array('fullname'=>'Lachlan'));
    $post = new Post(array('title'=>'My Post', 'author'=>$author));

    // save objects
    $author->save();
    $post->save();

    echo $post->title; // returns 'My Post'
    echo $post->Author->fullname; // returns 'Lachlan'


Pheasant supports one-to-one, and one-to-many relationship types.
