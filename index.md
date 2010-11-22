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

{% hightlight bash %}
pear channel-discover pearhub.org
pear install pearhub/Pheasant
{% endhighlight %}

Usage
-----

{% highlight php %}

use \Pheasant;
use \Pheasant\Types;

class Post extends Pheasant\DomainObject
{
  public static function initialize($builder, $pheasant)
  {
    $pheasant
      ->register(__CLASS__, new Pheasant\RowMapper('posts'))
      ;

    $builder
      ->properties(array(
        'postid'   => new Types\Sequence(),
        'title'    => new Types\String(255, 'required'),
        'authorid  => new Types\Integer(11),
      ))
      ;
  }
}

// configure pheasant
Pheasant::initialize('mysql://localhost:/mydatabase');

// create some objects
$post = new Post(array('title'=>'My Post'));
$post->save();

echo $post->title; // returns 'My Post'
{% endhighlight %}


Project Status
--------------

Pheasant is still very much alpha, as far as I know it's not being used in production.

