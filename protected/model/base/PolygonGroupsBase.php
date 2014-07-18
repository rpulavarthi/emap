<?php
Doo::loadCore('db/DooModel');

class PolygonGroupsBase extends DooModel{

    /**
     * @var int Max length is 11.
     */
    public $id;

    /**
     * @var int Max length is 11.
     */
    public $user_id;

    /**
     * @var varchar Max length is 255.
     */
    public $name;

    /**
     * @var date
     */
    public $create_date;

    public $_table = 'polygon_groups';
    public $_primarykey = 'id';
    public $_fields = array('id','user_id','name','create_date');

    public function getVRules() {
        return array(
                'id' => array(
                        array( 'integer' ),
                        array( 'maxlength', 11 ),
                        array( 'optional' ),
                ),

                'user_id' => array(
                        array( 'integer' ),
                        array( 'maxlength', 11 ),
                        array( 'optional' ),
                ),

                'name' => array(
                        array( 'maxlength', 255 ),
                        array( 'optional' ),
                ),

                'create_date' => array(
                        array( 'date' ),
                        array( 'optional' ),
                )
            );
    }

}