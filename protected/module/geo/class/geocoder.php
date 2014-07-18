<?php 

	class geocoder{
        public static $url = 'http://maps.google.com/maps/geo';
        const G_GEO_SUCCESS             = 200;
        const G_GEO_BAD_REQUEST         = 400;
        const G_GEO_SERVER_ERROR        = 500;
        const G_GEO_MISSING_QUERY       = 601;
        const G_GEO_MISSING_ADDRESS     = 601;
        const G_GEO_UNKNOWN_ADDRESS     = 602;
        const G_GEO_UNAVAILABLE_ADDRESS = 603;
        const G_GEO_UNKNOWN_DIRECTIONS  = 604;
        const G_GEO_BAD_KEY             = 610;
        const G_GEO_TOO_MANY_QUERIES    = 620;
		private $lat;
		private $lng;
		private $hspan;
		private $vspan;
		
		function __construct($lat, $lng, $vspan, $hspan){
			$this->lat = $lat;
			$this->lng = $lng;
			$this->hspan = $hspan;
			$this->vspan = $vspan;
		}
		
		private function performRequest($search, $output = 'xml')
        {
            $url = sprintf('%s?q=%s&output=%s&oe=utf-8&gl=en',
                           self::$url,
                           urlencode($search),
                           $output);
 
			if(isset($this->lat) && isset($this->lng))
				$url .= '&ll=' . $this->lat . ',' . $this->lng . '&spn=' . $this->vspan . ',' . $this->hspan;
			
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
 
            return $response;
        }
		
		public function geocode($search)
        {
            $response = $this->performRequest($search, 'xml');
            $xml      = new SimpleXMLElement($response);
            $status   = (int) $xml->Response->Status->code;
            switch ($status) {
                case self::G_GEO_SUCCESS:
					//Build placemarks
					foreach ($xml->Response->Placemark as $placemark)
                        $placemarks[] = placemark::FromSimpleXml($placemark);
 
                    return $placemarks;
 
                case self::G_GEO_UNKNOWN_ADDRESS:
                case self::G_GEO_UNAVAILABLE_ADDRESS:
                    return array();
 
                default:
					return array();
                 //   throw new Exception(sprintf('Google Geo error %d occurred', $status));
            }
        }
    }
?>
 
