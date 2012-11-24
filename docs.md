---
title: Using Pheasant
layout: docs
---

Installing
----------

**Option 1: Include the pre-built PHAR archive**

Download the latest PHP archive of Pheasant and include that in your code. The internal class-loader will be automatically configured.

{% highlight php %}
<?php

require_once('lib/pheasant.phar');

{% endhighlight %}

**Option 2: Using the built in class-loader**

Pheasant complies to PSR-0 standards, which means you can use it with most classloaders simply by including the `pheasant\lib` directory in your `include_path`. Alternately, Pheasant comes with a classloader:

{% highlight php %}
<?php

require_once('lib/pheasant/lib/Pheasant/ClassLoader.php');

$classloader = new \Pheasant\ClassLoader();
$classloader->register();
{% endhighlight %}


Domain Objects
-----------------------

Pheasant data objects are called domain objects. A definition from c2.com:

> a domain object is a logical container of purely domain
> information, usually represents a logical entity in the problem domain space

In our terms, a domain object is any object that has a unique identity and
can be mapped to the database. Pheasant is designed to make this easy.

### Defining Properties

The properties of an object are typed scalar attributes. They almost always
map directly to database columns.

{% highlight php %}
<?php

use \Pheasant;
use \Pheasant\Types;

class Post extends Pheasant\DomainObject
{
  public function properties()
  {
    return array(
      'postid'   => new Types\Sequence(),
      'title'    => new Types\String(255, 'required'),
      'authorid' => new Types\Integer(11)
    );
  }
}
{% endhighlight %}

You can see a few things from the code above:

- The Post class extends `Pheasant\DomainObject`
- The `properties` method defines what the properties a domain object has
- Data lengths and constraints can be provided with the Types.
- Identity is implicit by default, defining a sequence defines the primary key

Once you have a domain object defined, you need to setup Pheasant's internal
connections and then you are good to go:

{% highlight php %}
<?php

Pheasant::setup('mysql://user:pass@localhost:3306/mydb');

$post = new Post(array('title'=>'My Post'));
$post->save();

echo $post->title; // returns 'My Post'
{% endhighlight %}

As you can see here, Pheasant magically provides a constructor that takes
an array of initial values. This is optional, you can also get and set values
using property access.

Pheasant provides a number of magical static methods callable via tha class,
`save()` persists your object to the database. It handles either inserting
or updating in the database and generating a new id for the object. Easy, right?


Relationships
-------------

The hard bit with object mapping is representing relationships between objects. This
example shows how Pheasant does it:

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
            'status'   => new Types\Enum(array('closed','open')),
            'authorid' => new Types\Integer(11),
            );
    }

    public function relationships()
    {
        return array(
            'Author' => Author::hasOne('author_id');
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
            'Posts' => Post::hasOne('author_id')
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
{% endhighlight %}

Relationships are defined in the `relationships()` method and basically
define a special property (which by convention is uppercased) that accesses
a related object. This means you can chain related objects really simply, allowing
for what would normally be a complex query to be represented tersely. Pheasant
handles figuring out how to translate this to database queries.

Pheasant supports a number of relationships,

- One to One
- One to Many
- Belongs To

The missing one is Many to Many, which generally involves a joining table
and isn't presently supported. Check out the roadmap for more details.


Finding Domain Objects
----------------------

The aim for finders in pheasant is to let you do the common stuff very easily, and get out of your way for the hard stuff. Imagine you are querying the `Post` and `Author` objects we defined in the previous example.

### The Basics

The basic means for searching for objects centers around the find method:

{% highlight php %}
<?php

Post::find('authorid = ? AND title = ?', 42, 'Llamas Farming');
{% endhighlight %}

You can see this is very similar to the WHERE portion of a query. Translated to SQL this query would look like:

{% highlight sql %}
SELECT * FROM post WHERE authorid = 42 AND title = 'Llamas Farming';
{% endhighlight %}

The result of `find()` is a `Collection`. If all you want is a single object, use `one()` instead of `find()`.

Both `find()` and `one()` can take either the SQL-like syntax above, or an array
of key=>values that must be matched in the row.

{% highlight php %}
<?php

Post::find(array(
    'author' => $author,
    'title' => 'Food goes in here',
    'status' => array('review', 'deleted'),
));
{% endhighlight %}

The nested array for status provides a series of values to match, these are implemented as an `IN (x, y, z)` condition in SQL.

Finally, if you just want to find an object based on its primary key, you can do this:

{% highlight php %}
<?php

Post::findById(42);
{% endhighlight %}


### Magic findBy methods

So SQL is great, but often we like to be a bit more descriptive, or we like to shorten things down a bit for common methods. Now you could create a custom finder which encapsulates that logic in its own method like so:

{% highlight php %}
<?php

class PostFinder
{
    public function findByAuthorIdAndTitle($finder, $authorId, $title)
    {
        return $finder('authorid = ? AND title = ?', $authorId, $title);
    }
}

Pheasant::mixinFinder('Post', new PostFinder());

{% endhighlight %}

However we don't even need to do that, instead of defining a custom finder with that method, we can just go ahead and write:

{% highlight php %}
<?php

Post::findByAuthorIdAndTitle(42, 'Food goes in here');
{% endhighlight %}

This automatically translates that call into this query:

{% highlight sql %}
SELECT * FROM post WHERE authorid = 42 AND title = 'Food goes in here';
{% endhighlight %}

We can do OR queries as well like so:

{% highlight php %}
<?php

Post::findByAuthorIdOrStatus(42, 'available');
{% endhighlight %}

To make it a bit smarter, findBy also understands relationships e.g.

{% highlight php %}
<?php

Post::findByAuthorOrStatus($author, 'available');
{% endhighlight %}

This works exactly the same as the previous findBy call.

### Getting even fancier with findBy

Imagine you had a domain object like the following:

> UserPreference
> - id
> - type
> - userid
> - value

Now imagine you wanted to find out what a users particular preference was,
but you weren't sure if we'd even created one yet, you could do the following:

{% highlight php %}
<?php

UserPreference::findOrCreateByUserIdAndType(42, 'view');
{% endhighlight %}

This will run this query:

{% highlight sql %}
SELECT * FROM userpreference WHERE userid = 42 AND type = 'view' LIMIT 1;
{% endhighlight %}

Note that the query uses LIMIT 1 automatically, since a findOrCreate call always has to
return only one object. In the event that it doesn't find a match, rather than throwing
an exception it just creates a new domain object with those properties already set.

Want to find the most recent object that matches your query?

{% highlight php %}
<?php

Post::findLatestByAuthor($author);
{% endhighlight %}

Translates to

{% highlight sql %}
SELECT * FROM post WHERE authorid = 42 ORDER BY id DESC LIMIT 1;
{% endhighlight %}

Want to just get the first result of your query?

{% highlight php %}
<?php

Post::findOneByTitle('Food goes in here');
{% endhighlight %}

Translates to

{% highlight sql %}
SELECT * FROM post WHERE title = 'Food goes in here' LIMIT 1;
{% endhighlight %}

Want to find objects with one of several status'?

{% highlight php %}
<?php

Post::findByStatus(array('review', 'deleted'));
{% endhighlight %}

Translates to

{% highlight sql %}
SELECT * FROM post WHERE status IN ('review', 'deleted')
{% endhighlight %}



Events
------


Types
-----


Database Layer
--------------


Raw Queries
-----------


Extending Finders
-----------------


Sequences vs Auto Increment
---------------------------


FAQ
---



