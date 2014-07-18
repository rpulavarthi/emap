#!/usr/bin/perl
use Geo::Coordinates::UTM;
$zone = "";
my $latitude;
my $longitude;
while(<>) {
	chomp;
	#extract origin_lon and convert it to UTM zone
	#if there is no origin_lon, coords are already lat/lon
	#format is described at http://read.pudn.com/downloads138/sourcecode/others/592839/Mapinfo_Mif.pdf
	if (/CoordSys Earth Projection \d+, \d+, ".*?", ([+-\d]+)/) {
		$origin_lon = $1;
		$zone = (int(($origin_lon + 180)/6) + 1) % 60;
		$zone_string = $zone . "T"; #required by Geo::Coordinates::UTM package
	}

	if (/^\s*(\d+)\s*$/) {
    	$rows = $1;
        $poly = "|";
    	for($k = 1; $k <= $rows; $k++) {
            $rec = <>;
            chomp $rec;
			if ($zone > '') { #if a zone has been set, we have to cvt from Northing/Easting to lat/lon
				($easting, $northing) = $rec =~ m/([\d.]+) +([\d.]+)/;
				($latitude, $longitude) = utm_to_latlon('WGS-84',$zone_string,$easting,$northing);
			}
			else {
			#extract regular latlon here
                ($longitude, $latitude) = split (/ /, $rec);
			}
			$poly .= "$longitude,$latitude";
			$poly .= ";" if $k <= $rows;
		}
        $poly .= "|";
        print $poly;
	}
}
