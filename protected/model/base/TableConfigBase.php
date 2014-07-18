<?php
Doo::loadCore('db/DooModel');

class TableConfigBase extends DooModel{

    /**
     * @var varchar Max length is 255.
     */
    public $table_name;

    /**
     * @var varchar Max length is 45.
     */
    public $year;

    /**
     * @var varchar Max length is 45.
     */
    public $location;

    /**
     * @var varchar Max length is 45.
     */
    public $quarter;

    /**
     * @var int Max length is 11.
     */
    public $id;

    public $_table = 'table_config';
    public $_primarykey = 'id';
    public $_fields = array('table_name','year','location','quarter','id');

    public function getVRules() {
        return array(
                'table_name' => array(
                        array( 'maxlength', 255 ),
                        array( 'notnull' ),
                ),

                'year' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                ),

                'location' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                ),

                'quarter' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                ),

                'id' => array(
                        array( 'integer' ),
                        array( 'maxlength', 11 ),
                        array( 'optional' ),
                )
            );
    }

}