---
title: Using Pheasant
layout: docs
---


<h2 class="numbered" id="Installing">Installing</h2>

Pheasant should be installed via [Composer](http://getcomposer.org). See the details on
Pheasant's [packagist page](https://packagist.org/packages/lox/pheasant).

Once you've installed Pheasant as a dependancy of your project, you can simply load Pheasant
classes via Composer's autoload mechanism and configure your Mysql connection.

{% highlight php %}
<?php

// use composer's autoload mechanism
require_once('vendor/autoload.php');

// setup database connections
Pheasant::setup('mysql://user:pass@localhost:3306/mydb');

{% endhighlight %}


<h2 class="numbered" id="DomainObjects">Domain Objects</h2>

Anything mapped to the database is referred to as a `Domain Object` in Pheasant. The only requirement
for a domain object is that is has a unique identity (in database parlance, a primary key).

Creating a Domain Object is done by extending `Pheasant\DomainObject` and providing a `properties` method
that describes what properties should be persisted to the database. The database name is derived from the classname,
or you can override it with a `tableName` method.

{% highlight php %}
<?php

use \Pheasant\Types;

class Post extends \Pheasant\DomainObject
{
  public function properties()
  {
    return array(
      'postid'   => new Types\Integer(11, 'primary'),
      'title'    => new Types\String(255, 'required'),
      'authorid' => new Types\Integer(11)
    );
  }
}
{% endhighlight %}

The example above will persist to the `post` table, and will use a primary key of postid, which will be
an auto_increment field by default.

Once defined, a Pheasant object can be immediately used:

{% highlight php %}
<?php

$post = new Post(array('title'=>'My Post'));
$post->save();

echo $post->title; // returns 'My Post'
{% endhighlight %}

As you can see here, Pheasant provides a default constructor that takes
an array of initial values. This is optional, you can also get and set values
using property access.

Pheasant provides a number of magical static methods callable via tha class,
`save()` persists your object to the database. It handles either inserting
or updating in the database and generating a new id for the object. Easy, right?


<h3 class="numbered" id="Properties">Properties</h3>

Properties are defined as typed scalar attributes. They will almost always correspond 1-to-1 with
database columns.

Available types are:

 - Integer
 - String
 - Boolean
 - Character
 - Sequence

The only type here that doesn't map directly to it's MySQL equivelent is `Sequence`. Using this type
will use `sequence` table for the primary key.

Property definitions can take in options, either as a flat string or an `Pheasant\Options` object.

{% highlight php %}
<?php

new Types\String(255, 'required default=test');
new Types\String(255, new Pheasant\Options(array('required', 'default'=>'test'));


{% endhighlight %}

Either of the above styles are valid, use whichever you find to be the most readable.

Valid options are `required`, `notnull`, `default`, `auto_increment` and `primary`.


<h3 class="numbered" id="Relationships">Relationships</h3>

The hard bit with object mapping is representing relationships between objects. Pheasant
does this by defining a `relationships()` method.

{% highlight php %}
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
            'authorid' => new Types\Integer(11),
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

// create some objects
$author = new Author(array('fullname'=>'Lachlan'));
$post = new Post(array('title'=>'My Post', 'author'=>$author));

// save objects
$author->save();
$post->save();

echo $post->title; // returns 'My Post'
echo $post->Author->fullname; // returns 'Lachlan'
{% endhighlight %}

Relationships are defined in the `relationships()` method and basically
define a special property (which by convention is uppercased) that accesses
a related object. This means you can chain related objects really simply, allowing
for what would normally be a complex query to be represented tersely. Pheasant
handles figuring out how to translate this to database queries.

Pheasant supports one-to-one, and one-to-many relationship types.

<h3 class="numbered" id="Identity">Identity</h3>

<h3 class="numbered" id="LazyLoading">Lazy Loading</h3>

<h2 class="numbered" id="Finders">Finders</h2>

The aim for finders in pheasant is to let you do the common stuff very easily, and get out
of your way for the hard stuff. Imagine you are querying the `Post` and `Author` objects we
defined in the previous example.

{% highlight php %}
<?php

Post::find('authorid = ? AND title = ?', 42, 'Llamas Farming');
{% endhighlight %}

You can see this is very similar to the WHERE portion of a query. Translated to SQL this query
would look like:

{% highlight sql %}
SELECT * FROM post WHERE authorid = 42 AND title = 'Llamas Farming';
{% endhighlight %}

The result of `find()` is a `Pheasant\Collection`. If all you want is a single object, use
`one()` instead of `find()`.

Both `find()` and `one()` expect a `Criteria` object, although a string or an array are converted
to one for convenience. This means either the SQL-like syntax above or the array syntax below can be used.

{% highlight php %}
<?php

Post::find(array(
    'author' => $author,
    'title' => 'Food goes in here',
    'status' => array('review', 'deleted'),
));
{% endhighlight %}

The nested array for status provides a series of values to match, these are implemented as
an `IN (x, y, z)` condition in SQL.

Finally, if you just want to find an object based on its primary key, you can use `byId()`.


<h3 class="numbered" id="MagicFinders">Magic Finders</h3>

The vast majority of most finder methods follow the same pattern, so Pheasant provides a number
of dynamically generated finder methods to make your life easier.

{% highlight php %}
<?php

Post::findByAuthorIdAndTitle(42, 'My Book Title');
{% endhighlight %}

The above does exactly what you'd expect, it finds Posts with an Author and a Title. You can add any number
of attributes and any combination of and and or (rules of precendence apply).



