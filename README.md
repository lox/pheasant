Pheasant
=======================================

Pheasant is an object relational mapper written to take advantage of PHP 5.3. Simple relationships
are supported, with the emphasis being on scalability and performance over complexity.

The entire codebase will always be less than 3000 lines, excluding tests. Currently only compatible with
InnoDb/Mysql 5+.

Status of Development
---------------------------------

Still very much alpha. Presently at proof-of-concept phase, examples below are only
partially implemented.

- Mapping (working)
- Relationships (HasMany, HasOne and BelongsTo implemented)
- Custom Mappers/Finders (todo)
- Events (working)
- Raw Queries (partially working)
- Delete/Remove
- Documentation

Persisting Objects
---------------------------------

Each domain object has a set of properties and relationships that are defined in the
configure method. Each domain object delegates to a mapper object for the actual saving
and loading of objects.

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
				'status    => new Types\Enum(array('closed','open')),
				'authorid  => new Types\Integer(11),
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
	Pheasant::initialize('mysql://localhost:/mydatabase');

	// create some objects
	$author = new Author(array('fullname'=>'Lachlan'));
	$post = new Post(array('title'=>'My Post', 'author'=>$author));

	// save objects
	$author->save();
	$post->save();

	echo $post->title; // returns 'My Post'
	echo $post->Author->fullname; // returns 'Lachlan'

	?>

Raw Queries
---------------------------------

It's easy to take an existing query and hydrate the results into a domain object.

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

	use \Pheasant;
	use \Pheasant\Events;
	use \Pheasant\Types;

	class Post extends DomainObject
	{
		public static function configure($builder, $pheasant)
		{
			$pheasant
				->register(__CLASS__, new RowMapper('post'))
				;

			$builder
				->properties(array(
					'postid'      => new Types\Sequence(),
					'title'       => new Types\String(255),
					'timecreated' => new Types\Integer(11),
					));

			$builder
				->events(array(
					'beforeCreate' => function($e, $d) { $d->timecreated = time(); }
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

	use \Pheasant\Finder;

	class CustomPostFinder extends Finder\RowFinder
	{
		public function findByAuthorId($definition, $id)
		{
			return $this->find('author_id = ?', $id);
		}
	}

	$pheasant->registerFinder('Post', new CustomPostFinder('post'));

	// finds single posts by author id (magic methods still work)
	$posts = Post::findOneByAuthorId(55);

	?>
