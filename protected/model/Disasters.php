<?php
Doo::loadCore('db/DooModel');

class Disasters extends DooModel{

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

    /**
     * @var varchar Max length is 45.
     */
    public $type;
	
	/**
     * @var varchar Max length is 45.
     */
    public $icon;

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
    public $geometry;
	
	
    public $_table = 'disasters';
    public $_primarykey = 'id';
    public $_fields = array('id','client','name','url','type','geometry','icon','category','source');

    public function getVRules() {
        return array(
                'id' => array(
                        array( 'integer' ),
                        array( 'maxlength', 11 ),
                        array( 'notnull' ),
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
                ),

                'type' => array(
                        array( 'maxlength', 45 ),
                        array( 'notnull' ),
                ),
				
				'geometry' => array(
                        array( 'maxlength', 45 ),
                        array( 'notnull' ),
                ),

                'icon' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                ),
				'category' => array(
                        array( 'integer' ),
                        array( 'maxlength', 11 ),
                        array( 'notnull' ),
                ),
				 'source' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                )
				
            );
    }

}
