<?php
Doo::loadCore('db/DooModel');

class Users extends DooModel{

    /**
     * @var bigint Max length is 20.  unsigned.
     */
    public $id;

    /**
     * @var varchar Max length is 255.
     */
    public $username;

    /**
     * @var char Max length is 32.
     */
    public $password;

    /**
     * @var varchar Max length is 255.
     */
    public $email;

    /**
     * @var varchar Max length is 15.
     */
    public $client_ip;

    /**
     * @var timestamp
     */
    public $last_login_utc;

    /**
     * @var varchar Max length is 45.
     */
    public $client;

    /**
     * @var int Max length is 11.
     */
    public $cluster_zoom_level;

    /**
     * @var float
     */
    public $home_latitude;

    /**
     * @var float
     */
    public $home_longitude;

    /**
     * @var int Max length is 11.
     */
    public $home_zoom;

    /**
     * @var tinyint Max length is 4.
     */
    public $is_enabled;

    /**
     * @var tinyint Max length is 4.
     */
    public $change_password_on_login;

    /**
     * @var varchar Max length is 255.
     */
    public $default_basemap;

    /**
     * @var varchar Max length is 255.
     */
    public $default_layer;

    public $_table = 'users';
    public $_primarykey = 'id';
    public $_fields = array('id','username','password','email','client_ip','last_login_utc','client','cluster_zoom_level','home_latitude','home_longitude','home_zoom','is_enabled','change_password_on_login','default_basemap','default_layer');

    public function getVRules() {
        return array(
                'id' => array(
                        array( 'integer' ),
                        array( 'min', 0 ),
                        array( 'maxlength', 20 ),
                        array( 'optional' ),
                ),

                'username' => array(
                        array( 'maxlength', 255 ),
                        array( 'notnull' ),
                ),

                'password' => array(
                        array( 'maxlength', 32 ),
                        array( 'notnull' ),
                ),

                'email' => array(
                        array( 'maxlength', 255 ),
                        array( 'notnull' ),
                ),

                'client_ip' => array(
                        array( 'maxlength', 15 ),
                        array( 'optional' ),
                ),

                'last_login_utc' => array(
                        array( 'datetime' ),
                        array( 'notnull' ),
                ),

                'client' => array(
                        array( 'maxlength', 45 ),
                        array( 'notnull' ),
                ),

                'cluster_zoom_level' => array(
                        array( 'integer' ),
                        array( 'maxlength', 11 ),
                        array( 'notnull' ),
                ),

                'home_latitude' => array(
                        array( 'float' ),
                        array( 'optional' ),
                ),

                'home_longitude' => array(
                        array( 'float' ),
                        array( 'optional' ),
                ),

                'home_zoom' => array(
                        array( 'integer' ),
                        array( 'maxlength', 11 ),
                        array( 'optional' ),
                ),

                'is_enabled' => array(
                        array( 'integer' ),
                        array( 'maxlength', 4 ),
                        array( 'notnull' ),
                ),

                'change_password_on_login' => array(
                        array( 'integer' ),
                        array( 'maxlength', 4 ),
                        array( 'notnull' ),
                ),

                'default_basemap' => array(
                        array( 'maxlength', 255 ),
                        array( 'notnull' ),
                ),

                'default_layer' => array(
                        array( 'maxlength', 255 ),
                        array( 'optional' ),
                )
            );
    }

}