---
title: Pheasant
layout: default
---

<div class="container">
  <div class="hero-unit jumbotron">
    <h1 class="pheasant-title tk-bello-pro">Pheasant</h1>
    <p>Obvious data mapping for PHP 5.3+ and Mysqli. Small footprint, big impact.</p>
    <p><img class="pheasant-logo" src="/assets/images/pheasant-large.png" width="300px" alt="Pheasant"></p>
    <ul class="hero-links inline-list">
      <li><a href="http://github.com/lox/pheasant">GitHub</a></li>
      <li><a href="https://pheasant.readthedocs.org/en/latest/gettingstarted.html">Examples</a></li>
      <li>Version 1.0.0</li>
    </ul>
  </div>
</div>

<div class="container-narrow">
  <hr>
  <div class="marketing">
  <h2 class="tk-bello-pro">How?</h2>
  </div>

{% highlight php %}
<?php

class Post extends Pheasant\DomainObject
{
  public function properties()
  {
    return array(
        'postid'   => new Types\Integer(11, 'primary auto_increment'),
        'title'    => new Types\String(255, 'required'),
        'authorid' => new Types\Integer(11)
        );
  }
}

// simply create objects
$post = new Post(array('title'=>'My Post'));
$post->save();

echo $post->title; // returns 'My Post'

// look up via magic finders
$post = Post::findByTitle('My Post')->one();

{% endhighlight %}

</div>

<div class="container-narrow">
  <div class="marketing">
  <h2 class="tk-bello-pro">Why?</h2>
  </div>
  <div class="features row-fluid">
    <div class="feature-col span6">
      <h4>Lightweight</h4>
      <p>Small codebase, light abstractions that keep you close to the database you're developing for. Pheasant
      attempts to make what it's going to do as obvious as possible.</p>

      <h4>Memory Efficient</h4>
      <p>Designed for use with big database tables. Pheasant is routinely tested to measure performance and
      memory usage against other PHP ORM's.</p>
    </div>

    <div class="feature-col span6">
      <h4>Bare Metal</h4>
      <p>Pheasant is MySQL 5.1+ / Innodb only, with only a thin layer of convenience over
       <a href="http://php.net/manual/en/book.mysqli.php">mysqli</a>. This makes features like SSL connections,
       asynchronous queries and granular control over buffering possible with less code.</p>

      <h4>Road Tested</h4>
      <p>Helping power high volume websites like <a href="http://99designs.com">99designs</a>.</p>
    </div>
    </div>
</div>


