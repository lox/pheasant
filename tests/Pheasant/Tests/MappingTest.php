<?php

namespace Pheasant\Tests;

use \Pheasant\Types;
use \Pheasant\Tests\Examples\Post;

class BasicMappingTest extends \Pheasant\Tests\MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->table('post', array(
            'postid' => new Types\IntegerType(11, 'primary auto_increment'),
            'title' => new Types\StringType(255, 'required'),
            'subtitle' => new Types\StringType(255),
            ));
    }

    public function testBasicSaving()
    {
        $post = new Post('First post, bitches!');
        $post->subtitle = 'Just because...';

        $this->assertEquals((string) $post->postid, null);
        $this->assertInstanceOf('\Pheasant\Identity', $post->identity());
        $this->assertEquals(array('title','subtitle'), array_keys($post->changes()));
        $this->assertFalse($post->isSaved());
        $post->save();

        $this->assertTrue($post->isSaved());
        $this->assertEquals(array(), $post->changes());
        $this->assertEquals($post->postid, 1);
        $this->assertEquals($post->title, 'First post, bitches!');
        $this->assertEquals($post->subtitle, 'Just because...');

        $post->title = 'Another title, perhaps';
        $this->assertTrue($post->isSaved());
        $this->assertEquals(array('title'), array_keys($post->changes()));
        $post->save();

        $this->assertEquals(array(), $post->changes());
        $this->assertEquals($post->title, 'Another title, perhaps');
    }

    public function testSequentialSave()
    {
        $post1 = new Post('First post');
        $post2 = new Post('Second post');

        $this->assertEquals($post1->title, 'First post');
        $this->assertEquals($post2->title, 'Second post');

        $post1->save();
        $post2->save();

        $this->assertEquals($post1->title, 'First post');
        $this->assertEquals($post2->title, 'Second post');
    }

    public function testImport()
    {
        $posts = Post::import(array(
            array('title'=>'First Post'),
            array('title'=>'Second Post'),
            ));

        $this->assertEquals(count($posts), 2);
        $this->assertEquals($posts[0]->postid, 1);
        $this->assertEquals($posts[1]->postid, 2);
        $this->assertEquals($posts[0]->title, 'First Post');
        $this->assertEquals($posts[1]->title, 'Second Post');
        $this->assertTrue($posts[0]->isSaved());
        $this->assertTrue($posts[1]->isSaved());
    }

    public function testPropertyReferences()
    {
        $post = new Post('first post');
        $future = $post->postid;

        $this->assertTrue(is_object($future));
        $this->assertNull($future->value());
        $this->assertNull($post->get('postid'));
        $post->save();

        $this->assertEquals($post->postid, 1);
        $this->assertEquals($future->value(), 1);
        $this->assertEquals($post->get('postid'), 1);
    }

    public function testDeleting()
    {
        $post = Post::create('first post');

        $this->assertEquals($post->postid, 1);
        $this->assertEquals($post->title, 'first post');

        $post->delete();
        $this->assertRowCount(0, "SELECT * FROM post WHERE postid=1");
    }
}
