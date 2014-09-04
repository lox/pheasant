Pheasant
=======================================

Pheasant is an object relational mapper written to take advantage of PHP 5.3. Simple relationships
are supported, with the emphasis being on scalability and performance over complexity.

Pheasant doesn't attempt to abstract the database and makes heavy use of
MySQL/Innodb features.

More details available at http://getpheasant.com

Status of Development
---------------------------------

Running in production on 99designs.com. See `ROADMAP` for more details on future plans.

[![Build Status](https://travis-ci.org/lox/pheasant.png)](https://travis-ci.org/lox/pheasant)

Installing
---------------------------------

Easiest way is to install via composer http://packagist.org/packages/lox/pheasant.

Persisting Objects
---------------------------------

Each domain object has a set of properties and relationships that are defined in the
configure method. Each domain object delegates to a mapper object for the actual saving
and loading of objects.

```php
<?php

use \Pheasant;
use \Pheasant\Types;

class Post extends DomainObject
{
  public function properties()
  {
    return array(
      'postid'   => new Types\Sequence(),
      'title'    => new Types\String(255, 'required'),
      'subtitle' => new Types\String(255),
      'status'   => new Types\Enum(array('closed','open')),
      'authorid' => new Types\Integer(11),
      );
  }

  public function relationships()
  {
    return array(
      'Author' => Author::hasOne('authorid')
    );
  }
}

class Author extends DomainObject
{
  public function properties()
  {
    return array(
      'authorid' => new Types\Sequence(),
      'fullname' => new Types\String(255, 'required')
    );
  }

  public function relationships()
  {
    return array(
      'Posts' => Post::hasOne('authorid')
    );
  }
}

// configure database connection
Pheasant::setup('mysql://localhost:/mydatabase');

// create some objects
$author = new Author(array('fullname'=>'Lachlan'));
$post = new Post(array('title'=>'My Post', 'author'=>$author));

// save objects
$author->save();
$post->save();

echo $post->title; // returns 'My Post'
echo $post->Author->fullname; // returns 'Lachlan'
```

Magical Finders
---------------------------------

Many variations of finders are available for locating objects:

```php
<?php

// all users
$users = User::all();

// all users named frank
$users = User::find('firstname = ?', 'frank');

// any fields can be used in finders, this translates to above
$users = User::findByFirstName('frank');

// a single user named frank
$users = User::one('firstname = ?', 'frank');

// a user by primary key
$user = User::byId(1);

```

Collection Scoping
------------------------------------
Scoping allows you to specify commonly-used queries which can be referenced as method calls on Collection objects. All scope methods will return a Pheasant::Collection object which will allow for further methods (such as other scopes) to be called on it.

To define a simple scope, we first define a `scopes` method in our `DomainObject` that returns an associative array in `"methodName" => $closure` form.

```php
use \Pheasant;
Class User extends DomainObject
{
  public function scopes()
  {
    return array(
      'active' => function($collection){
        $collection->filter('last_login_date >= ?', strtotime('30 days ago'));
      },
    );
  }
}

// Scopes may be used by invoking them like methods
User::all()->active()
//=> Returns all active users
```

Events
---------------------------------

Code can be triggered before and after create, update and delete operations.

```php
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
```

Optionally, domain objects provide the following implicit hooks which can be overriden:

- afterCreate
- beforeUpdate, afterUpdate

Transactions
------------------------------------

Transactions can be created globally:

```php
<?php


\Pheasant::transaction(function() {
  $post = new Post(array('title'=>'First Post!'));
  $post->save();
});

```

Or transactions can be invoked on an instance:

```php
<?php

$post = new Post(array('title'=>'First Post!'));

$post->transaction(function($obj) {
  $obj->save();
});

```

Contributors
------------

Many thanks to @dhotson, @michaeldewildt, @rbone, @harto, @jorisleker, @tombb, @Jud, @bjornpost, @creativej
