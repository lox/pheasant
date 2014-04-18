<?php
namespace Pheasant\Tests\Examples;

class Project extends \Pheasant\DomainObject {
    public function tableName() {
        return 'projecten';
    }

    public function properties() {
        return array(
            'id' => new \Pheasant\Types\Integer(5, 'primary auto_increment'),
            'naam' => new \Pheasant\Types\String(255),
        );
    }
}
