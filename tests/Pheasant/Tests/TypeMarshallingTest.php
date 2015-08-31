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
                'id' => new Types\IntegerType(null, 'primary auto_increment'),
                'type' => new Types\StringType(128),
                'isllama' => new Types\BooleanType(array('default'=>true)),
                'weight' => new Types\DecimalType(5, 1),
                'timecreated' => new Types\DateTimeType(),
                'unixtime' => new Types\UnixTimestampType(),
                'camelidvariant' => new Types\StringType(128, array('allowed'=>array('llama', 'alpaca'))),
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

    public function testDecimalTypesAreUnmarshalled()
    {
        $object = new DomainObject(array('type'=>'Llama', 'weight' => 88.5));
        $object->save();

        $llamaById = DomainObject::byId(1);
        $this->assertSame($llamaById->weight, 88.5);
    }

    public function testBooleanTypesAreUnmarshalled()
    {
        $object = new DomainObject(array('type'=>'Llama'));
        $object->save();

        $llamaById = DomainObject::byId(1);
        $this->assertTrue($llamaById->isllama);
        $this->assertSame($llamaById->id, 1);
        $this->assertSame($llamaById->isllama, true);
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

    /**
     * @expectedException InvalidArgumentException
     */
    public function testStringAllowedValuesAreEnforced()
    {
        $object = new DomainObject(array('camelidvariant' => 'squirrel'));
        $object->save();
    }

    public function testStringAllowedValues()
    {
        $object = new DomainObject(array('camelidvariant' => 'llama'));
        $object->save();

        $llamaById = DomainObject::byId(1);
        $this->assertSame($llamaById->camelidvariant, 'llama');
    }

    public function testDecimalTypesAreMarshalledCorrectInLocale()
    {
        $prevLocale = setlocale(LC_ALL, '');

        /**
         * Locale with decimal_point = ","
         * So a float 88.5 becomes 88,5.
         */
        setlocale(LC_ALL, 'nl_NL');

        $object = new DomainObject(array('type'=>'Llama', 'weight' => 88.5));
        $object->save();

        $llamaById = DomainObject::byId(1);
        $this->assertSame($llamaById->weight, 88.5);

        setlocale(LC_ALL, $prevLocale);
    }

    public function testVariableIsNullable() {
        $object = new DomainObject;
        $object->weight = 88.5; // var type = double
        $object->weight = ''; // var type = string
        $object->weight = null;

        $this->assertSame($object->weight, null);
    }
}
