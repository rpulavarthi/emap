<?php
Doo::loadCore('db/DooModel');

class Links extends DooModel{

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
    public $name;

    /**
     * @var varchar Max length is 255.
     */
    public $url;

    public $_table = 'links';
    public $_primarykey = 'id';
    public $_fields = array('id','client','name','url');

    public function getVRules() {
        return array(
                'id' => array(
                        array( 'integer' ),
                        array( 'maxlength', 11 ),
                        array( 'optional' ),
                ),

                'client' => array(
                        array( 'maxlength', 45 ),
                        array( 'notnull' ),
                ),

                'name' => array(
                        array( 'maxlength', 45 ),
                        array( 'notnull' ),
                ),

                'url' => array(
                        array( 'maxlength', 255 ),
                        array( 'notnull' ),
                )
            );
    }

}