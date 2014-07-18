<?php
Doo::loadCore('db/DooModel');

class DisastersBase extends DooModel{

    /**
     * @var int Max length is 11.
     */
    public $id;

    /**
     * @var varchar Max length is 45.
     */
    public $name;

    /**
     * @var varchar Max length is 45.
     */
    public $icon;

    /**
     * @var varchar Max length is 45.
     */
    public $type;

    /**
     * @var varchar Max length is 255.
     */
    public $url;

    /**
     * @var int Max length is 11.
     */
    public $category;

    /**
     * @var varchar Max length is 45.
     */
    public $source;

    /**
     * @var varchar Max length is 45.
     */
    public $client;

    public $_table = 'disasters';
    public $_primarykey = 'id';
    public $_fields = array('id','name','icon','type','url','category','source','client');

    public function getVRules() {
        return array(
                'id' => array(
                        array( 'integer' ),
                        array( 'maxlength', 11 ),
                        array( 'optional' ),
                ),

                'name' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                ),

                'icon' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                ),

                'type' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                ),

                'url' => array(
                        array( 'maxlength', 255 ),
                        array( 'optional' ),
                ),

                'category' => array(
                        array( 'integer' ),
                        array( 'maxlength', 11 ),
                        array( 'optional' ),
                ),

                'source' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                ),

                'client' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                )
            );
    }

}