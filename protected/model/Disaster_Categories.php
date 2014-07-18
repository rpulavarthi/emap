<?php
Doo::loadCore('db/DooModel');

class Disaster_Categories extends DooModel{

    /**
     * @var int Max length is 11.
     */
    public $id;

    /**
     * @var varchar Max length is 45.
     */
    public $name;

	
    public $_table = 'disaster_categories';
    public $_primarykey = 'id';
    public $_fields = array('id','name');

    public function getVRules() {
        return array(
                'id' => array(
                        array( 'integer' ),
                        array( 'maxlength', 11 ),
                        array( 'notnull' ),
                ),
                'name' => array(
                        array( 'maxlength', 45 ),
                        array( 'notnull' ),
                )
            );
    }

}

