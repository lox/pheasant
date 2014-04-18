<?php

namespace Pheasant\Tests\Relationships;

use \Pheasant\Tests\Examples\Veldwerkdag;
use \Pheasant\Tests\Examples\Project;

class IncludesTestCase extends \Pheasant\Tests\MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        $migrator = new \Pheasant\Migrate\Migrator();
        $migrator
            ->create('project_testdata', Veldwerkdag::schema())
            ->create('projecten', Project::schema())
            ;

        $projecten = array(
            (new Project(array('naam' => 'Foo')))->save(),
            (new Project(array('naam' => 'Bar')))->save(),
            (new Project(array('naam' => 'Baz')))->save(),
            (new Project(array('naam' => 'Moo')))->save(),
        );

        $veldwerkdagen = array(
            (new Veldwerkdag(array('datum' => new \DateTime('+1 week'), 'Project' => $projecten[0])))->save(),
            (new Veldwerkdag(array('datum' => new \DateTime('+2 weeks'), 'Project' => $projecten[2])))->save()
        );

    }

    public function testBasicIncludes()
    {
        $testdays = Veldwerkdag::all()->limit(5)->includes([ 'Project' ]);

        foreach($testdays as $testday) {
            echo $testday->id."\n";
            echo $testday->Project->id."\n\n";

            // Please var_dump($schema->hash($object, array($this->local))) in Relationships/BelongsTo.php
            // to see that the key it is looking for is "Pheasant\Tests\Examples\Project[projectID=1]" while
            // you expect it to be "Pheasant\Tests\Examples\Project[id=1]"
        }
    }
}
