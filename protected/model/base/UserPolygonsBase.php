<?php
Doo::loadCore('db/DooModel');

class UserPolygonsBase extends DooModel{

    /**
     * @var int Max length is 11.
     */
    public $id;

    /**
     * @var int Max length is 11.
     */
    public $user_id;

    /**
     * @var int Max length is 11.
     */
    public $user_group;

    /**
     * @var varchar Max length is 255.
     */
    public $name;

    /**
     * @var varchar Max length is 255.
     */
    public $bounds;

    /**
     * @var polygon
     */
    public $geometry;

    /**
     * @var varchar Max length is 64.
     */
    public $color;

    /**
     * @var tinyint Max length is 1.
     */
    public $public;

    /**
     * @var date
     */
    public $create_date;

    public $_table = 'user_polygons';
    public $_primarykey = 'id';
    public $_fields = array('id','user_id','user_group','name','bounds','geometry','color','public','create_date');

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

                'user_group' => array(
                        array( 'integer' ),
                        array( 'maxlength', 11 ),
                        array( 'optional' ),
                ),

                'name' => array(
                        array( 'maxlength', 255 ),
                        array( 'optional' ),
                ),

                'bounds' => array(
                        array( 'maxlength', 255 ),
                        array( 'optional' ),
                ),

                'geometry' => array(
                        array( 'optional' ),
                ),

                'color' => array(
                        array( 'maxlength', 64 ),
                        array( 'optional' ),
                ),

                'public' => array(
                        array( 'integer' ),
                        array( 'maxlength', 1 ),
                        array( 'optional' ),
                ),

                'create_date' => array(
                        array( 'date' ),
                        array( 'optional' ),
                )
            );
    }

}