<?php
Doo::loadCore('db/DooModel');

class Layers extends DooModel{

    /**
     * @var int Max length is 11.
     */
    public $id;

    /**
     * @var varchar Max length is 255.
     */
    public $client;

    /**
     * @var varchar Max length is 255.
     */
    public $name;

    /**
     * @var varchar Max length is 255.
     */
    public $host;

    /**
     * @var varchar Max length is 255.
     */
    public $url;

    /**
     * @var varchar Max length is 255.
     */
    public $type;

    /**
     * @var varchar Max length is 255.
     */
    public $options;

    /**
     * @var int Max length is 11.
     */
    public $order;

    /**
     * @var varchar Max length is 255.
     */
    public $search_url;

    /**
     * @var varchar Max length is 255.
     */
    public $grid_url;
	
	/**
     * @var varchar Max length is 255.
     */
    public $search_group;
	
	/**
     * @var varchar Max length is 45.
     */
    public $pg_key;

    public $_table = 'layers';
    public $_primarykey = 'id';
    public $_fields = array('id','client','name','host','url','type','options','order','search_url','grid_url', 'search_group', 'pg_key');

    public function getVRules() {
        return array(
                'id' => array(
                        array( 'integer' ),
                        array( 'maxlength', 11 ),
                        array( 'optional' ),
                ),

                'client' => array(
                        array( 'maxlength', 255 ),
                        array( 'notnull' ),
                ),

                'name' => array(
                        array( 'maxlength', 255 ),
                        array( 'notnull' ),
                ),

                'host' => array(
                        array( 'maxlength', 255 ),
                        array( 'notnull' ),
                ),

                'url' => array(
                        array( 'maxlength', 255 ),
                        array( 'notnull' ),
                ),

                'type' => array(
                        array( 'maxlength', 255 ),
                        array( 'notnull' ),
                ),

                'options' => array(
                        array( 'maxlength', 255 ),
                        array( 'optional' ),
                ),

                'order' => array(
                        array( 'integer' ),
                        array( 'maxlength', 11 ),
                        array( 'optional' ),
                ),

                'search_url' => array(
                        array( 'maxlength', 255 ),
                        array( 'optional' ),
                ),

                'grid_url' => array(
                        array( 'maxlength', 255 ),
                        array( 'optional' ),
                ),
				
				'search_group' => array(
                        array( 'maxlength', 255 ),
                        array( 'optional' ),
                ),
				
				'pg_key' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                )
            );
    }

}