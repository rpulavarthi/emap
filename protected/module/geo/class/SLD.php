<?php

class SLD {

	public  static function getStyleSymbolizer($sld, $data, $geom_type){

		//check for Rules For Style
		$sldRules = $sld['sld:StyledLayerDescriptor']['sld:NamedLayer']['sld:UserStyle']['sld:FeatureTypeStyle']['sld:Rule'];
		
		$symbolizerArray = self::matchRules($sldRules, $geom_type, $data);		
		
		//var_dump($symbolizerArray);exit;
		
		if($symbolizerArray == null)
			return null;
		
		
		$symbolizer = null;
		//build proper symbolizer and return
		if(preg_match('/Polygon/', $geom_type))
			$symbolizer = self::polygonSymbolizer($symbolizerArray);
		
		if(preg_match('/Line/', $geom_type))
			$symbolizer = isset($lineSymbolizer['sld:Stroke']['sld:CssParamter']) ? $lineSymbolizer['sld:Stroke']['sld:CssParamter'] : null;
		
		if(preg_match('/Point/', $geom_type))
			$symbolizer = self::pointSymbolizer($symbolizerArray);


		return $symbolizer;
	}
	
	private static function matchRules($sldRules, $geom_type, $data ){
	

		foreach($sldRules as $rule){
		
			$symbolizerArray = null;
			//get symbolizer for geom_type
			
			if(preg_match('/Polygon/', $geom_type))
				$symbolizerArray = isset($rule['sld:PolygonSymbolizer']) ? $rule['sld:PolygonSymbolizer'] : null;
				
			if(preg_match('/Line/', $geom_type))
				$symbolizerArray = isset($rule['sld:LineSymbolizer']) ? $rule['sld:LineSymbolizer'] : null;
			
			if(preg_match('/Point/', $geom_type))
				$symbolizerArray = isset($rule['sld:PointSymbolizer']) ? $rule['sld:PointSymbolizer'] : null;

			if($symbolizerArray == null)
				return null;
				

		
			//process filter
			$filter = isset($rule['ogc:Filter']) ? $rule['ogc:Filter'] : null;
			if($filter == null)
				return $symbolizerArray;
		
			
			//Check for logical operators (AND/OR)
			if(isset($filter['ogc:And']) || isset($filter['ogc:Or'])){
			
				$logicalOperator = null;
				$filterKeys = null;
				
				$logicalOperatorArr = array_keys($filter);
				//only one logical operator allowed per filter group
				$logicalOperator = $logicalOperatorArr[0];
				switch($logicalOperator){
					case 'ogc:And':
						$filterKeys = array_keys($filter['ogc:And']);
					break;
					case 'ogc:Or':
						$filterKeys = array_keys($filter['ogc:Or']);
					break;
				}
				
				$result = array();
				foreach($filterKeys as $key){

					if(Utils::is_assoc($filter[$logicalOperator][$key])){
						$result[] = self::matchFilter($filter[$logicalOperator][$key], $key, $data);
					}else{
						foreach($filter[$logicalOperator][$key] as $i=>$value){
							$result[] = self::matchFilter($filter[$logicalOperator][$key][$i], $key, $data);
						}
					
					}
				}
				
				//check results
				$success = false;
				foreach($result as $res){
				
					if($logicalOperator == 'ogc:And'){
						$success = true;
						if(!$res){
							$success = false;
							break;
						}
					}
					
					if($logicalOperator == 'ogc:Or'){
						if($res){
							$success = true;
							break;
						}
					}
				}
				
				if($success)
					return $symbolizerArray;
		
			}else{
				$filterKeys = array_keys($filter);
				if(self::matchFilter($filter[$filterKeys[0]], $filterKeys[0],  $data))
					return $symbolizerArray;
					
			}
		}
	}
	
	private static function matchFilter($filterProperty, $key, $data){
			/*
		switch($key){
			case 'ogc:PropertyIsEqualTo':
				$property = $filterProperty['ogc:PropertyIsEqualTo']['ogc:PropertyName'];
				$literal  = $filterProperty['ogc:PropertyIsEqualTo']['ogc:Literal'];
				
				if(isset($data[$property]) && $data[$property] == $literal){
					return true;
				}
			break;
			case 'ogc:PropertyIsNotEqualTo':
				$property = $filterProperty['ogc:PropertyIsNotEqualTo']['ogc:PropertyName'];
				$literal  = $filterProperty['ogc:PropertyIsNotEqualTo']['ogc:Literal'];
				
				if(isset($data[$property]) && $data[$property] != $literal){
					return true;
				}
			break;
			case 'ogc:PropertyIsLessThan':
				$property = $filterProperty['ogc:PropertyIsLessThan']['ogc:PropertyName'];
				$literal  = $filterProperty['ogc:PropertyIsLessThan']['ogc:Literal'];
				
				if(isset($data[$property]) && $data[$property]  < $literal){
					return true;
				}
			break;
			case 'ogc:PropertyIsLessThanOrEqualTo':
				$property = $filterProperty['ogc:PropertyIsLessThanOrEqualTo']['ogc:PropertyName'];
				$literal  = $filterProperty['ogc:PropertyIsLessThanOrEqualTo']['ogc:Literal'];
				if(isset($data[$property]) && $data[$property]  <= $literal){
					return true;
				}
			break;
			case 'ogc:PropertyIsGreaterThan':
				$property = $filterProperty['ogc:PropertyIsGreaterThan']['ogc:PropertyName'];
				$literal  = $filterProperty['ogc:PropertyIsGreaterThan']['ogc:Literal'];
				
				if(isset($data[$property]) && $data[$property]  > $literal){
					return true;
				}
		
			break;
			case 'ogc:PropertyIsGreaterThanOrEqualTo':
				$property = $filterProperty['ogc:PropertyIsGreaterThanOrEqualTo']['ogc:PropertyName'];
				$literal  = $filterProperty['ogc:PropertyIsGreaterThanOrEqualTo']['ogc:Literal'];
				
				if(isset($data[$property]) && $data[$property] >= $literal){
					return true;
				}
			break;
			case 'ogc:PropertyIsNull':
				$property = $filterProperty['ogc:PropertyIsNull']['ogc:PropertyName'];
				$literal  = $filterProperty['ogc:PropertyIsNull']['ogc:Literal'];
				
				if(isset($data[$property]) && empty($data[$property])){
					return true;
				}
			break;
			case 'ogc:PropertyIsBetween':
				$property = $filter[$logicalOperator]['ogc:PropertyIsBetween']['ogc:PropertyName'];
				$lowerBoundary  = $filterProperty['ogc:PropertyIsBetween']['ogc:PropertyName']['ogc:LowerBoundary'];
				$upperBoundary  = $filterProperty['ogc:PropertyIsBetween']['ogc:PropertyName']['ogc:UpperBoundary'];
				
				if(isset($data[$property]) && ($data[$property] > $lowerBoundary && $data[$property] < $upperBoundary)){
					return true;
				}
			break;
			case 'ogc:PropertyIsLike':
				$property = $filterProperty['ogc:PropertyIsBetween']['ogc:PropertyName'];
				$literal  = $filterProperty['ogc:PropertyIsBetween']['ogc:Literal'];
				
				if(isset($data[$property]) && preg_match($literal, $data[$property])){
					return true;
				}
			break;
		}
		return false;
		
		*/
		
		
		switch($key){
			case 'ogc:PropertyIsEqualTo':
				$property = $filterProperty['ogc:PropertyName'];
				$literal  = $filterProperty['ogc:Literal'];
				
				if(isset($data[$property]) && $data[$property] == $literal){
					return true;
				}
			break;
			case 'ogc:PropertyIsNotEqualTo':
				$property = $filterProperty['ogc:PropertyName'];
				$literal  = $filterProperty['ogc:Literal'];
				
				if(isset($data[$property]) && $data[$property] != $literal){
					return true;
				}
			break;
			case 'ogc:PropertyIsLessThan':
				$property = $filterProperty['ogc:PropertyName'];
				$literal  = $filterProperty['ogc:Literal'];
				
				if(isset($data[$property]) && $data[$property]  < $literal){
					return true;
				}
			break;
			case 'ogc:PropertyIsLessThanOrEqualTo':
				$property = $filterProperty['ogc:PropertyName'];
				$literal  = $filterProperty['ogc:Literal'];
				if(isset($data[$property]) && $data[$property]  <= $literal){
					return true;
				}
			break;
			case 'ogc:PropertyIsGreaterThan':
				$property = $filterProperty['ogc:PropertyName'];
				$literal  = $filterProperty['ogc:Literal'];
				
				if(isset($data[$property]) && $data[$property]  > $literal){
					return true;
				}
		
			break;
			case 'ogc:PropertyIsGreaterThanOrEqualTo':
				$property = $filterProperty['ogc:PropertyName'];
				$literal  = $filterProperty['ogc:Literal'];
				
				if(isset($data[$property]) && $data[$property] >= $literal){
					return true;
				}
			break;
			case 'ogc:PropertyIsNull':
				$property = $filterProperty['ogc:PropertyName'];
				$literal  = $filterProperty['ogc:Literal'];
				
				if(isset($data[$property]) && empty($data[$property])){
					return true;
				}
			break;
			case 'ogc:PropertyIsBetween':
				$property = $filter[$logicalOperator]['ogc:PropertyName'];
				$lowerBoundary  = $filterProperty['ogc:PropertyName']['ogc:LowerBoundary'];
				$upperBoundary  = $filterProperty['ogc:PropertyName']['ogc:UpperBoundary'];
				
				if(isset($data[$property]) && ($data[$property] > $lowerBoundary && $data[$property] < $upperBoundary)){
					return true;
				}
			break;
			case 'ogc:PropertyIsLike':
				$property = $filterProperty['ogc:PropertyName'];
				$literal  = $filterProperty['ogc:Literal'];
				
				if(isset($data[$property]) && preg_match($literal, $data[$property])){
					return true;
				}
			break;
		}
		return false;
	}

	public static function polygonSymbolizer($polygonSymbolizer){

		$sym = new polygonSymbolizer;

		// Fill Params
		if(isset($polygonSymbolizer['sld:Fill']['sld:CssParameter'])){
			foreach($polygonSymbolizer['sld:Fill']['sld:CssParameter'] as $i=>$param){
				if(!is_array($param)){
					$name = $polygonSymbolizer['sld:Fill']['sld:CssParameter'][$i."_attr"]["name"];
					switch($name){
						case 'fill':
							$sym->fill = $param;
						break;
						case 'fill-opacity':
							$sym->fill_opacity = $param;
						break;
					}
				}
			}
		}
	
	
	
		//Stroke Params
		if(isset($polygonSymbolizer['sld:Stroke']['sld:CssParameter'])){
			foreach($polygonSymbolizer['sld:Stroke']['sld:CssParameter'] as $i=>$param){
				if(!is_array($param)){
					$name = $polygonSymbolizer['sld:Stroke']['sld:CssParameter'][$i."_attr"]["name"];
					switch($name){
						case 'stroke':
							$sym->stroke = $param;
						break;
						case 'stroke-opacity':
							$sym->stroke_opacity = $param;
						break;
						case 'stroke-width':
							$sym->stroke_width = $param;
						break;
						case 'stroke-dasharray':
							$sym->stroke_dasharray = $param;
						break;
					}
				}
			}
		}
		
		return $sym;
	
	}

	
	public static function pointSymbolizer($pointSymbolizer){
	
		$sym = new pointSymbolizer;
		
	
		// Fill Params
		if(isset($pointSymbolizer['sld:Graphic']['sld:Mark']['sld:Fill']['sld:CssParameter'])){
		

			if(is_array($pointSymbolizer['sld:Graphic']['sld:Mark']['sld:Fill']['sld:CssParameter'])){
				foreach($pointSymbolizer['sld:Graphic']['sld:Mark']['sld:Fill']['sld:CssParameter'] as $i=>$param){
					if(!is_array($param)){
						$name = $pointSymbolizer['sld:Graphic']['sld:Mark']['sld:Fill']['sld:CssParameter'][$i."_attr"]["name"];
						switch($name){
							case 'fill':
								$sym->fill = $param;
							break;
							case 'fill-opacity':
								$sym->fill_opacity = $param;
							break;
						}
					}
				}
			} else{
			
			//TODO Fix for multi params
				$sym->fill = $pointSymbolizer['sld:Graphic']['sld:Mark']['sld:Fill']['sld:CssParameter'];

			}
		}
	
		//Size
		if (isset($pointSymbolizer['sld:Graphic']['sld:Size']))
			$sym->size = $pointSymbolizer['sld:Graphic']['sld:Size'];

		return $sym;
	}
}
?>