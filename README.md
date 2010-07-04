
Pheasant
=======================================

Pheasant is a object-mapper written to take advantage of PHP 5.3. It's inspired by
Martin Fowler's DataMapper pattern. Simple relationships are supported, with the
emphasis being on scalability and performance over complexity.

Status of Development
---------------------------------

Still very much alpha. Presently at proof-of-concept phase, examples below are only
partially implemented.

- Mapping (working)
- Relationships (partially working)
- Custom Mappers/Finders (todo)
- Events (todo)
- Unit of Work (todo)

Persisting Objects
---------------------------------

Each domain object has a set of properties and relationships that are defined in the
configure method. Each domain object delegates to a mapper object for the actual saving
and loading of objects.

	<?php

	use pheasant;

	class Post extends DomainObject
	{
		public static function configure($schema, $props, $rels)
		{
			$schema
				->table('post')
				;

			$props
				->serial('postid', array('primary'=>true))
				->string('title', 255, array('required'=>true))
				->string('subtitle', 255)
				->enum('status', array('closed','open'))
				;

			$rels
				->hasOne('Author', 'Author', 'author_id')
				;
		}
	}

	class Author extends DomainObject
	{
		public static function configure($schema, $props, $rels)
		{
			$schema
				->table('Author')
				;

			$props
				->serial('authorid', array('primary'=>true))
				->string('fullname', 255, array('required'=>true))
				;

			$rels
				->belongsTo('Posts', 'Post')
				;
		}
	}

	Pheasant::setup('mysql://localhost:/mydatabase');

	// create some objects
	$author = new Author(array('fullname'=>'Lachlan'));
	$post = new Post(array('title'=>'My Post', 'author'=>$author);
	$post->save();

	?>

Querying Objects
---------------------------------

	<?php

	use pheasant;
	use pheasant\query;

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
		printf("User %s has posts about llamas\n',$user->fullname,$user->Posts
	}

	?>

Events
---------------------------------

Code can be triggered before and after create, update and delete operations.

	<?php

	use pheasant;
	use pheasant\events;

	class Post extends DomainObject
	{
		public static function configure($schema, $props, $rels, $events)
		{
			$schema
				->table('post')
				->event(Events::PRE_CREATE, 'preCreate')
				;

			$props
				->serial('postid', array('primary'=>true))
				->string('title', 255, array('required'=>true))
				->timestamp('timecreated')
				;
		}

		private function preCreate()
		{
			// sets a timestamp
			$this->timestamp = time();
		}
	}

	?>

Custom Finders
---------------------------------

Finders and mappers are decoupled from each other, so implementing custom finder methods
is straight forward.

	<?php

	use pheasant;

	class PostFinder extends Finder
	{
		public function findByAuthorId($definition, $id)
		{
			return $this->find('author_id = ?', $id);
		}
	}

	Pheasant::defineFinder('Post',new PostFinder());

	// finds single posts by author id
	$post = Post::findByAuthorId(55)->one();

	?>

Transactions
---------------------------------

Executing multiple actions in a transaction are facilitated with unit of work objects.

	<?php

	$user1 = User::findOneById(1);
	$user2 = User::findOneByFirstName('Frank');

	// create a script
	$unit = new UnitOfWork();

	// prepare the unit of work
	$user1->type = 'admin';
	$user2->type = 'admin';
	$unit->save($user1, $user2);

	// execute in a transaction
	$unit->execute();

	?>