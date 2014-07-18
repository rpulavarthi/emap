<?php
class polygonSymbolizer{
	public $fill;
	public $fill_opacity;
	public $stroke;
	public $stroke_width;
	public $stroke_opacity;
	public $stroke_dasharray;
	
	function __construct(){
		//Fill Defaults
		$this->fill 		= '#AAAAFF';
		$this->fill_opacity = 1;

		//Stroke Defaults
		$this->stroke 			= '#000000';
		$this->stroke_width 	= 1.5;
		$this->stroke_opacity   = 1;
		//TODO
		$this->stroke_style = '';
	}
}
?>