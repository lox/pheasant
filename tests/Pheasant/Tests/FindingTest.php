<?php

namespace Pheasant\Tests;

use \Pheasant\Query\Criteria;
use \Pheasant;
use \Pheasant\Tests\Examples\User;
use \Pheasant\Tests\Examples\UserUnderscore;
use \Pheasant\Tests\Examples\UserPref;
use \Pheasant\Tests\Examples\UserUnderscorePref;


class FindingTestCase extends \Pheasant\Tests\MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        $migrator = new \Pheasant\Migrate\Migrator();
        $migrator
            ->create('user', User::schema())
            ->create('userpref', UserPref::schema())
            ->create('userunderscore', UserUnderscore::schema())
            ->create('userunderscorepref', UserUnderscorePref::schema())
            ;

        // create some users
        $this->users = User::import(array(
            array('firstname'=>'Frank','lastname'=>'Castle'),
            array('firstname'=>'Cletus','lastname'=>'Kasady')
        ));
        $this->users_underscore = UserUnderscore::import(array(
            array('first_name'=>'Frank','last_name'=>'Castle'),
            array('first_name'=>'Cletus','last_name'=>'Kasady')
        ));

        // create some user prefs
        $this->userprefs = UserPref::import(array(
            array('User'=>$this->users[0],'pref'=>'autologin','value'=>'yes'),
            array('User'=>$this->users[1],'pref'=>'autologin','value'=>'no')
        ));
        $this->user_underscore_prefs = UserUnderscorePref::import(array(
            array('User'=>$this->users_underscore[0],'pref'=>'autologin','value'=>'yes'),
            array('User'=>$this->users_underscore[1],'pref'=>'autologin','value'=>'no')
        ));


        $this->assertTrue($this->userprefs[0]->User->equals($this->users[0]));
        $this->assertTrue($this->userprefs[1]->User->equals($this->users[1]));

        $this->assertTrue($this->user_underscore_prefs[0]->User->equals($this->users_underscore[0]));
        $this->assertTrue($this->user_underscore_prefs[1]->User->equals($this->users_underscore[1]));
    }

    public function testFindAll()
    {
        $users = User::find();
        $array = iterator_to_array($users);

        $this->assertEquals(2, $users->count());
        $this->assertEquals(2, count($array));
        $this->assertInstanceOf('\Pheasant\Tests\Examples\User', $array[0]);
        $this->assertInstanceOf('\Pheasant\Tests\Examples\User', $array[1]);
        $this->assertTrue($array[0]->equals($this->users[0]));
        $this->assertTrue($array[1]->equals($this->users[1]));
    }

    public function testAllIsAnAliasOfFind()
    {
        $users = User::all();
        $this->assertEquals(2, $users->count());
    }

    public function testFindMany()
    {
        $users = User::find("lastname = ? and firstname = ?", 'Kasady', 'Cletus');
        $this->assertEquals(count($users), 1);
        $this->assertEquals($users[0]->firstname, 'Cletus');
        $this->assertEquals($users[0]->lastname, 'Kasady');
    }

    public function testFindOne()
    {
        $cletus = User::one('lastname = ?', 'Kasady');
        $this->assertEquals($cletus->firstname, 'Cletus');
        $this->assertEquals($cletus->lastname, 'Kasady');
    }

    public function testFindManyByCriteria()
    {
        $users = User::find(new Criteria("lastname = ?", array('Kasady')));
        $this->assertEquals(count($users), 1);
        $this->assertEquals($users[0]->firstname, 'Cletus');
        $this->assertEquals($users[0]->lastname, 'Kasady');
    }

    public function testFindManyByMagicalColumn()
    {
        $users = User::findByLastName('Kasady');
        $this->assertEquals(count($users), 1);
        $this->assertEquals($users[0]->firstname, 'Cletus');
        $this->assertEquals($users[0]->lastname, 'Kasady');
    }

    public function testFindManyByMagicalUnderscoreColumn()
    {
        $users = UserUnderscore::findByLastName('Kasady');
        $this->assertEquals(count($users), 1);
        $this->assertEquals($users[0]->first_name, 'Cletus');
        $this->assertEquals($users[0]->last_name, 'Kasady');
    }

    public function testFindManyByMultipleMagicalColumns()
    {
        $users = User::findByLastNameOrFirstName('Kasady', 'Frank');
        $this->assertEquals(count($users), 2);
    }

    public function testFindManyByMultipleMagicalUnderscoreColumns()
    {
        $users = UserUnderscore::findByLastNameOrFirstName('Kasady', 'Frank');
        $this->assertEquals(count($users), 2);
    }

    public function testFindById()
    {
        $cletus = User::byId(2);
        $this->assertEquals($cletus->firstname, 'Cletus');
        $this->assertEquals($cletus->lastname, 'Kasady');
    }

    public function testOneByMagicalColumn()
    {
        $cletus = User::oneByFirstName('Cletus');
        $this->assertEquals($cletus->firstname, 'Cletus');
        $this->assertEquals($cletus->lastname, 'Kasady');
    }

    public function testOneByMagicalUnderscoreColumn()
    {
        $cletus = UserUnderscore::oneByFirstName('Cletus');
        $this->assertEquals($cletus->first_name, 'Cletus');
        $this->assertEquals($cletus->last_name, 'Kasady');
    }

    public function testFindByIn()
    {
        $cletus = User::one('lastname = ?', array('Llamas','Kasady'));
        $this->assertEquals($cletus->firstname, 'Cletus');
        $this->assertEquals($cletus->lastname, 'Kasady');
    }

    // ----------------------------------
    // Test other collection methods

    public function testFilter()
    {
        User::import(array(
            array('firstname'=>'Frank','lastname'=>'Beechworth'),
            ));

        $users = User::find()
            ->filter("firstname like ?", 'Fra%')
            ->filter("lastname in (?)", 'Castle')
            ;

        $this->assertEquals(count($users), 1);
        $this->assertEquals($users[0]->firstname, 'Frank');
        $this->assertEquals($users[0]->lastname, 'Castle');
    }

    public function testFilterViaInvoke()
    {
        $users = User::find();
        $filtered = $users("firstname = ?", 'Frank');

        $this->assertEquals(count($filtered), 1);
        $this->assertEquals($filtered[0]->firstname, 'Frank');
        $this->assertEquals($filtered[0]->lastname, 'Castle');
    }

    public function testLimit()
    {
        $users = User::find()->limit(1);

        $this->assertEquals(count($users), 1);
        $this->assertEquals($users[0]->firstname, 'Frank');
        $this->assertEquals($users[0]->lastname, 'Castle');
    }

    // Bugs

    public function testSavedStatusAfterFind()
    {
        $users = User::find('userid = 1');

        $this->assertTrue($users[0]->isSaved());
        $this->assertEquals($users[0]->changes(), array());
    }
}
