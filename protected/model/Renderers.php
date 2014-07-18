<?php
Doo::loadModel('base/DooModel');

class Renderers extends DooModel{
/**
     * @var int Max length is 11.
     */
    public $id;

    /**
     * @var varchar Max length is 45.
     */
    public $client;

    /**
     * @var varchar Max length is 45.
     */
    public $key;

    /**
     * @var varchar Max length is 45.
     */
    public $name;
	
	/**
     * @var enum data type - click,text.
     */
    public $type;

    public $_table = 'renderers';
    public $_primarykey = 'id';
    public $_fields = array('id','client','key','name', 'type');

    public function getVRules() {
        return array(
                'id' => array(
                        array( 'integer' ),
                        array( 'maxlength', 11 ),
                        array( 'notnull' ),
                ),

                'client' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                ),

                'key' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                ),

                'name' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
				),
				'type' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                )
            );
    }
}