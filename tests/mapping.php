<?php

namespace pheasant\tests\mapping;

use pheasant\DomainObject;
use pheasant\Pheasant;

require_once('autorun.php');
require_once(__DIR__.'/base.php');

class Post extends DomainObject
{
	private function configure($schema, $props, $rels)
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

	private function construct($title)
	{
		$this->title = 'title';
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
		$post = new Post('First post, bitches!');
		$post->subtitle = 'Just because...';

		$this->assertEqual((string) $post->postid, null);
		$this->assertIsA($post->identity(), '\pheasant\Identity');
		$this->assertIsA($post->postid, '\pheasant\Future');
		$this->assertFalse($post->isSaved());
		$post->save();

		/*
		$this->assertTrue($post->isSaved());
		$this->assertEqual($post->postid, 1);
		$this->assertEqual($post->title, 'First post, bitches!');
		$this->assertEqual($post->subtitle, 'Just because...');
		*/
	}
}

