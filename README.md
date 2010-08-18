
Pheasant
=======================================

Pheasant is an object relational mapper written to take advantage of PHP 5.3. Simple relationships
are supported, with the emphasis being on scalability and performance over complexity.

The entire codebase will always be less than 5000 lines, excluding tests. Currently only compatible with
InnoDb/Mysql 5+.

Status of Development
---------------------------------

Still very much alpha. Presently at proof-of-concept phase, examples below are only
partially implemented.

- Mapping (working)
- Relationships (HasMany implemented, HasOne still to-do)
- Custom Mappers/Finders (todo)
- Events (todo)

Persisting Objects
---------------------------------

Each domain object has a set of properties and relationships that are defined in the
configure method. Each domain object delegates to a mapper object for the actual saving
and loading of objects.

	<?php

	use Pheasant;

	class Post extends DomainObject
	{
		public static function configure($builder)
		{
			$builder
				->properties(array(
					'postid' => Serial(array('primary'=>true)),
					'title' => String(255, array('required'=>true)),
					'subtitle' => String(255),
					'status = Enum(array('closed','open')),
					'authorid => Integer(11),
				))
				->relationships(array(
					'Author' => HasOne('Author', 'author_id')
				));
		}
	}

	class Author extends DomainObject
	{
		public static function configure($builder)
		{
			$builder
				->properties(array(
					'authorid' => Serial(array('primary'=>true)),
					'fullname' => String(255, array('required'=>true))
					))
				->relationships(array(
					'Posts' => HasOne('Post', 'author_id')
					))
		}
	}

	// configure pheasant
	$pheasant = new Pheasant('mysql://localhost:/mydatabase');
	$pheasant->configure('Author', new Mapper\RowMapper('author'));

	// create some objects
	$author = new Author(array('fullname'=>'Lachlan'));
	$post = new Post(array('title'=>'My Post', 'author'=>$author));

	// save objects
	$author->save();
	$post->save();

	?>

Querying Objects
---------------------------------

	<?php

	use Pheasant\Query;

	// all users
	$users = User::find();

	// all users named frank
	$users = User::find('firstname = ?', 'frank');

	// this requires two queries
	foreach(User::find() as $user)
	{
		printf("User %s has %d posts\n", $user->fullname, $user->Posts->count());
	}

	// custom queries for complex joins
	$query = new Query();
	$query
		->from('user u')
		->innerJoin('post p', 'on u.userid=p.userid and p.title like ?', array('Llama%'))
		;

	// builds in one query
	foreach($query as $user)
	{
		printf("User %s has posts about llamas\n',$user->fullname,$user->Posts);
	}

	?>

Events
---------------------------------

Code can be triggered before and after create, update and delete operations.

	<?php

	use Pheasant\Events;

	class Post extends DomainObject
	{
		public static function configure($builder)
		{
			$builder
				->properties(array(
					'postid' => Serial(array('primary'=>true)),
					'title' => String(255),
					'timecreated' => Integer(11),
					));

			$builder
				->events(array(
					'after_create' => function($d) { $d->timecreated = time(); }
				));
		}
	}

	?>

Optionally, domain objects can have the methods afterCreate, beforeUpdate, afterUpdate,
beforeDelete, afterDelete and they will be implicitly called.

Custom Finder Methods
---------------------------------

Finders and mappers are decoupled from each other, so implementing custom finder methods
is straight forward.

	<?php

	class CustomPostFinder extends Finder\RowFinder
	{
		public function findByAuthorId($definition, $id)
		{
			return $this->find('author_id = ?', $id);
		}
	}

	$pheasant->configureFinder('Post', new CustomPostFinder('post'));

	// finds single posts by author id (magic methods still work)
	$posts = Post::findOneByAuthorId(55);

	?>
