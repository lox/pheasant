<?php

namespace Pheasant\Tests;

use \Pheasant\Mapper\RowMapper;
use \Pheasant\DomainObject;
use \Pheasant\Tests\Examples\Animal;
use \Pheasant\Types;

class TypeMarshallingTest extends \Pheasant\Tests\MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        // set up a domain object
        $this->initialize('Pheasant\DomainObject', function($builder) {
            $builder->properties(array(
                'id' => new Types\Integer(null, 'primary auto_increment'),
                'type' => new Types\String(128),
                'timecreated' => new Types\DateTime(),
                'unixtime' => new Types\UnixTimestamp(),
            ));
        });

        // set up tables
        $this->pheasant->register('Pheasant\DomainObject', new RowMapper('domainobject'));
        $this->migrate('domainobject', DomainObject::schema());
    }

    public function testIntegerTypesAreUnmarshalled()
    {
        $object = new DomainObject(array('type'=>'Llama'));
        $object->save();

        $llamaById = DomainObject::byId(1);
        $this->assertSame($llamaById->id, 1);
        $this->assertSame($llamaById->type, 'Llama');
    }

    public function testDateTimeTypesAreRoundTripped()
    {
        $ts = new \DateTime();
        $object = new DomainObject(array('type'=>'Llama'));
        $object->timecreated = $ts;
        $object->save();

        $this->assertRowCount(1, "SELECT * FROM domainobject WHERE timecreated='".$ts->format('c')."'");

        $llamaById = DomainObject::byId(1);
        $this->assertSame($llamaById->id, 1);
        $this->assertSame($llamaById->type, 'Llama');
        $this->assertSame($llamaById->timecreated->getTimestamp(), $ts->getTimestamp());
    }


    public function testUnixTimestampTypesAreRoundTripped()
    {
        $ts = new \DateTime();

        $object = new DomainObject(array('type'=>'Llama'));
        $object->unixtime = $ts;
        $object->save();

        $this->assertRowCount(1, "SELECT * FROM domainobject WHERE unixtime='".$ts->getTimestamp()."'");

        $llamaById = DomainObject::byId(1);
        $this->assertSame($llamaById->id, 1);
        $this->assertSame($llamaById->type, 'Llama');
        $this->assertSame($llamaById->unixtime->getTimestamp(), $ts->getTimestamp());
    }

}
