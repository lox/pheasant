

Pheasant (An ORM inspired by Martin Fowler)

Pheasant is a lightweight data mapper designed to take advantage of PHP 5.3+. It's key goals
are simplicity and performance, Pheasant is written to work with large datasets with small amounts
of memory. MySQL 5.x is targeted specifically to avoid database abstraction.

Things I like:

- Mapper/Finders decoupled from DomainObjects
- Attribute accessors rather than getters/setters
- No dependancies other than SPL and mysqlnd
- Constructors don't require chaining to parent classes
- Chainable methods

Things I don't like:

- SQL-like languages that need to be transformed into SQL
- Persistence coupled heavily to domain objects
- Huge inheritance hierarchies
- Too much magic


Examples:

	// adding a configure method defines an object
	class User extends DomainObject
	{
		private function configure()
		{
			$this->addAttributes(array(
				'userid' => array(Serial(), array('primary'=>true)),
				'firstname' => String(255),
				'lastname' => String(255),
				));

			$this->addRelationships(array(
				'Group'=>HasOne('Group', 'groupid')
				));
		}
	}

	// creating an object and setting keys
	$user = new User();
	$user->firstname = 'Test';
	$user->lastname = 'Testerson';

	$user = new User();
	$user->set(array(
		'firstname'=>'Test',
		'lastname'=>'Testerson',
	));

	$user = new User();
	$user
		->set('firstname','Test')
		->set('lastname','Testerson')

	// save a domain object (still not sure about this bit)
	User::save($user);
	Pheasant::mapper($user)->save($user);

	// querying
	foreach(User::findBySql("firstname like ?", array('%T%')) as $user)
	{
		// do stuff with user object
	}

	// load 1-1 relationships with 1 query
	foreach(User::findAll()->join('Group') as $user)
	{
		echo $user->Group->groupid;
	}

	// delete all objects
	User::delete(User::findAll());

	// raw sql
	$resultSet = ResultSet("SELECT * FROM user", new User());
	foreach($resultSet as $user)
	{
		// do stuff with user
	}

	// resultset hydration with closures
	$resultSet = ResultSet("SELECT * FROM user");
	$resultSet->setHydrator(function($row){
		return new User()->set($row);
	});

