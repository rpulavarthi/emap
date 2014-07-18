<?php
Doo::loadCore('db/DooModel');

class EnodTableConfigBase extends DooModel{

    /**
     * @var int Max length is 11.
     */
    public $ID;

    /**
     * @var varchar Max length is 45.
     */
    public $data_source;

    /**
     * @var varchar Max length is 45.
     */
    public $year;

    /**
     * @var varchar Max length is 45.
     */
    public $data_source_name;

    /**
     * @var varchar Max length is 500.
     */
    public $query_parameters;

    /**
     * @var varchar Max length is 500.
     */
    public $query_params_values;

    /**
     * @var varchar Max length is 255.
     */
    public $carrier;

    /**
     * @var varchar Max length is 255.
     */
    public $client;

    public $_table = 'enod_table_config';
    public $_primarykey = 'ID';
    public $_fields = array('ID','data_source','year','data_source_name','query_parameters','query_params_values','carrier','client');

    public function getVRules() {
        return array(
                'ID' => array(
                        array( 'integer' ),
                        array( 'maxlength', 11 ),
                        array( 'notnull' ),
                ),

                'data_source' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                ),

                'year' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                ),

                'data_source_name' => array(
                        array( 'maxlength', 45 ),
                        array( 'optional' ),
                ),

                'query_parameters' => array(
                        array( 'maxlength', 500 ),
                        array( 'optional' ),
                ),

                'query_params_values' => array(
                        array( 'maxlength', 500 ),
                        array( 'optional' ),
                ),

                'carrier' => array(
                        array( 'maxlength', 255 ),
                        array( 'optional' ),
                ),

                'client' => array(
                        array( 'maxlength', 255 ),
                        array( 'optional' ),
                )
            );
    }

}