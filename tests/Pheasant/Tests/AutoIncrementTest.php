<?php

namespace Pheasant\Tests;

use \Pheasant\Mapper\RowMapper;
use \Pheasant\DomainObject;
use \Pheasant\Types;

class AutoIncrementTest extends \Pheasant\Tests\MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        // set up a domain object
        $this->initialize('Pheasant\DomainObject', function($builder) {
            $builder->properties(array(
                'id' => new Types\IntegerType(null, 'primary auto_increment'),
                'value' => new Types\StringType(),
            ));
        });

        // set up tables
        $this->pheasant->register('Pheasant\DomainObject', new RowMapper('domainobject'));
        $this->migrate('domainobject', DomainObject::schema());
    }

    public function testPrimaryKeyPersistAfterSave_Bug57()
    {
        $object = DomainObject::create(array('value'=>'llama'));
        $this->assertEquals(1, $object->id);

        $found = DomainObject::byId(1);
        $found->value = 'alpaca';
        $found->save();

        $this->assertEquals(1, $found->id);
    }
}
