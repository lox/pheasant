<?php

namespace Pheasant\Tests\Mapping;

use \Pheasant\DomainObject;
use \Pheasant\Types;
use \Pheasant\Mapper\RowMapper;

require_once('autorun.php');
require_once(__DIR__.'/base.php');

class Post extends DomainObject
{
	public static function initialize($builder, $pheasant)
	{
		$pheasant
			->register(__CLASS__, new RowMapper('post'));

		$builder
			->properties(array(
				'postid' => new Types\Integer(11, 'primary auto_increment'),
				'title' => new Types\String(255, 'required'),
				'subtitle' => new Types\String(255),
			));
	}

	public function construct($title)
	{
		$this->title = $title;
	}
}

class BasicMappingTestCase extends \Pheasant\Tests\MysqlTestCase
{
	public function setUp()
	{
		$this->table('post', array(
			'postid' => new Types\Integer(11, 'primary auto_increment'),
			'title' => new Types\String(255, 'required'),
			'subtitle' => new Types\String(255),
			));
	}

	public function testBasicSaving()
	{
		$post = new Post('First post, bitches!');
		$post->subtitle = 'Just because...';

		$this->assertEqual((string) $post->postid, null);
		$this->assertIsA($post->identity(), '\Pheasant\Identity');
		$this->assertEqual(array('title','subtitle'), array_keys($post->changes()));
		$this->assertFalse($post->isSaved());
		$post->save();

		$this->assertTrue($post->isSaved());
		$this->assertEqual(array(), $post->changes());
		$this->assertEqual($post->postid, 1);
		$this->assertEqual($post->title, 'First post, bitches!');
		$this->assertEqual($post->subtitle, 'Just because...');

		$post->title = 'Another title, perhaps';
		$this->assertTrue($post->isSaved());
		$this->assertEqual(array('title'), array_keys($post->changes()));
		$post->save();

		$this->assertEqual(array(), $post->changes());
		$this->assertEqual($post->title, 'Another title, perhaps');
	}

	public function testSequentialSave()
	{
		$post1 = new Post('First post');
		$post2 = new Post('Second post');

		$this->assertEqual($post1->title, 'First post');
		$this->assertEqual($post2->title, 'Second post');

		$post1->save();
		$post2->save();

		$this->assertEqual($post1->title, 'First post');
		$this->assertEqual($post2->title, 'Second post');
	}

	public function testImport()
	{
		$posts = Post::import(array(
			array('title'=>'First Post'),
			array('title'=>'Second Post'),
			));

		$this->assertEqual(count($posts), 2);
		$this->assertEqual($posts[0]->postid, 1);
		$this->assertEqual($posts[1]->postid, 2);
		$this->assertEqual($posts[0]->title, 'First Post');
		$this->assertEqual($posts[1]->title, 'Second Post');
		$this->assertTrue($posts[0]->isSaved());
		$this->assertTrue($posts[1]->isSaved());
	}
}

