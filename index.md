---
title: Pheasant
layout: default
---

What?
----

Pheasant is a simple object mapper for PHP 5.3+ and MySQL 5+. It offers basic relationships and query hydration.

Why?
----

Pheasant is designed to be light and fast. It doesn't provide database abstraction and only supports a basic set
of relationships. Magic doesn't scale, neither do giant codebases. Pheasant aims to make pragmatic compromises
and not try and do everything. More than 3,000 lines is too much!

Installation
------------

The project is available on [github](https://github.com/lox/pheasant), or available via pear:

{% highlight bash %}
$ pear channel-discover pearhub.org
$ pear install pearhub/Pheasant
{% endhighlight %}

Usage
-----

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

// configure database connection
Pheasant::initialize('mysql://user:pass@localhost:/mydb');

$post = new Post(array('title'=>'My Post'));
$post->save();

echo $post->title; // returns 'My Post'
{% endhighlight %}

More documentation is available, along with examples in the [README](https://github.com/lox/pheasant/blob/master/README.md).


Project Status
--------------

Pheasant is still under development and isn't in any production use, yet!
