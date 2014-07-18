<?php
Doo::loadCore('db/DooModel');

class LogBase extends DooModel{

    /**
     * @var int Max length is 11.
     */
    public $id;

    /**
     * @var varchar Max length is 45.
     */
    public $username;

    /**
     * @var varchar Max length is 15.
     */
    public $ip_address;

    /**
     * @var varchar Max length is 15.
     */
    public $client_ip;

    /**
     * @var timestamp
     */
    public $login_date;

    public $_table = 'log';
    public $_primarykey = 'id';
    public $_fields = array('id','username','ip_address','client_ip','login_date');

    public function getVRules() {
        return array(
                'id' => array(
                        array( 'integer' ),
                        array( 'maxlength', 11 ),
                        array( 'optional' ),
                ),

                'username' => array(
                        array( 'maxlength', 45 ),
                        array( 'notnull' ),
                ),

                'ip_address' => array(
                        array( 'maxlength', 15 ),
                        array( 'notnull' ),
                ),

                'client_ip' => array(
                        array( 'maxlength', 15 ),
                        array( 'notnull' ),
                ),

                'login_date' => array(
                        array( 'datetime' ),
                        array( 'notnull' ),
                )
            );
    }

}