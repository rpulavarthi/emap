<?php
Doo::loadCore('db/DooModel');

class DisasterFtBase extends DooModel{

    /**
     * @var int Max length is 11.
     */
    public $id;

    /**
     * @var varchar Max length is 45.
     */
    public $owner;

    /**
     * @var tinyint Max length is 4.
     */
    public $active;

    /**
     * @var datetime
     */
    public $modifydate;

    /**
     * @var varchar Max length is 45.
     */
    public $title;

    /**
     * @var text
     */
    public $notes;

    /**
     * @var varchar Max length is 45.
     */
    public $client;

    /**
     * @var geometry
     */
    public $shape;

    public $_table = 'disaster_ft';
    public $_primarykey = 'id';
    public $_fields = array('id','owner','active','modifydate','title','notes','client','shape');

    public function getVRules() {
        return array(
                'id' => array(
                        array( 'integer' ),
                        array( 'maxlength', 11 ),
                        array( 'optional' ),
                ),

                'owner' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                ),

                'active' => array(
                        array( 'integer' ),
                        array( 'maxlength', 4 ),
                        array( 'optional' ),
                ),

                'modifydate' => array(
                        array( 'datetime' ),
                        array( 'optional' ),
                ),

                'title' => array(
                        array( 'maxlength', 45 ),
                        array( 'notnull' ),
                ),

                'notes' => array(
                        array( 'optional' ),
                ),

                'client' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                ),

                'shape' => array(
                        array( 'optional' ),
                )
            );
    }

}