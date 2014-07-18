<?php
    class point
    {
        private $_lat;
        private $_lng;

        public function __construct($latitude, $longitude)
        {
            $this->_lat = $latitude;
            $this->_lng = $longitude;
        }

        public function getLatitude()
        {
            return $this->_lat;
        }

        public function getLongitude()
        {
            return $this->_lng;
        }

        public static function Create($str)
        {
            list($longitude, $latitude, $elevation) = explode(',', $str, 3);

            return new self($latitude, $longitude);
        }
    }
?>