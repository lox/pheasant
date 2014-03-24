<?php
namespace Pheasant\Tests\Examples;

class Veldwerkdag extends \Pheasant\DomainObject {

    public function tableName() {
        return 'project_testdata';
    }

    public function properties() {
        return array(
            'id' => new \Pheasant\Types\Integer(11, 'primary auto_increment'),
            'projectID' => new \Pheasant\Types\Integer(5),
            'datum' => new \Pheasant\Types\DateTime()
        );
    }

    public function relationships() {
        return array(
            'Project' => Project::belongsTo('projectID', 'id'),
        );
    }
}
