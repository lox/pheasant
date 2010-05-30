<?php

namespace pheasant\tests\mapping;

use pheasant\DomainObject;
use pheasant\Pheasant;

require_once('autorun.php');
require_once(__DIR__.'/base.php');

class Post extends DomainObject
{
	protected function configure($schema, $props, $rels)
	{
		$schema
			->table('post')
			;

		$props
			->serial('postid', array('primary'))
			->string('title', 255, array('required'))
			->string('subtitle', 255)
			;
	}
}

class BasicMappingTestCase extends \pheasant\tests\MysqlTestCase
{
	public function setUp()
	{
		$table = Pheasant::connection()->table('post');
		$table
			->integer('postid', 4, array('auto_increment', 'primary'))
			->string('title')
			->string('subtitle')
			->create()
			;

		$this->assertTrue($table->exists());
	}

	public function testBasicSaving()
	{
		$post = new Post();
		$post->title = 'First post, bitches!';
		$post->subtitle = 'Just because...';

		$this->assertEqual((string) $post->postid, null);
		$this->assertIsA($post->identity(), '\pheasant\Identity');
		$this->assertFalse($post->isSaved());

		$post->save();
		$this->assertEqual($post->postid, 1);
		$this->assertEqual($post->title, 'First post, bitches!');
		$this->assertEqual($post->subtitle, 'Just because...');
	}
}

