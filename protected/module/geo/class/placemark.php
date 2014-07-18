<?php
    class placemark
    {
        const ACCURACY_UNKNOWN      = 0;
        const ACCURACY_COUNTRY      = 1;
        const ACCURACY_REGION       = 2;
        const ACCURACY_SUBREGION    = 3;
        const ACCURACY_TOWN         = 4;
        const ACCURACY_POSTCODE     = 5;
        const ACCURACY_STREET       = 6;
        const ACCURACY_INTERSECTION = 7;
        const ACCURACY_ADDRESS      = 8;

        protected $_point;
        protected $_address;
        protected $_accuracy;

        public function setAddress($address)
        {
            $this->_address = (string) $address;
        }

        public function getAddress()
        {
            return $this->_address;
        }

        public function __toString()
        {
            return $this->getAddress();
        }

        public function setPoint(Point $point)
        {
            $this->_point = $point;
        }

        public function getPoint()
        {
            return $this->_point;
        }

        public function setAccuracy($accuracy)
        {
            $this->_accuracy = (int) $accuracy;
        }

        public function getAccuracy()
        {
            return $this->_accuracy;
        }

        public static function FromSimpleXml($xml)
        {
            $point = point::Create($xml->Point->coordinates);

            $placemark = new self;
            $placemark->setPoint($point);
            $placemark->setAddress($xml->address);
            $placemark->setAccuracy($xml->AddressDetails['Accuracy']);

            return $placemark;
        }
    }
?>