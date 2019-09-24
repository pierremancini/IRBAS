<?php 


/**
* Create Geographical entities 
*/
class GeographicalEntity
{

	/**
	 * It is the name of column in the .csv file
	 * @var
	 */
	private $type;

	/**
	 * The entitie's name 
	 * @var
	 */
	private $value;

	/**
	 * 
	 * @var
	 */
	private $latitude;
	
	/**
	 * 
	 * @var
	 */
	private $longitude;


	/**
	 * Son geographical entity in the hierarchy of geographical entities
	 * @var
	 */
	private $son;

	
	function __construct($type,$value,$latitude,$longitude,$son=NULL)
	{
		//All _ are repalced by space string to match with the type names of the data base (cf. site_mod.geographical_entity_type)
		if(preg_match('#^(.*)?_?(.*)_name$#',$type))
		{
			$explodedType=explode('_',$type);
			if(count($explodedType)==3){
				$this->type=$explodedType[0].' '.$explodedType[1];
			}else{
				$this->type=$explodedType[0];
			}
			
		}else{
			$this->type = $type;
		}
		
		$this->value = $value;
		$this->latitude = $latitude;
		$this->longitude = $longitude;
		$this->son = $son;
	}

	/**
	 * 
	 * 
	 */
	public function getLongitude()
	{
		return $this->longitude;
	}

	/**
	 * 
	 * 
	 */
	public function setLongitude($longitude)
	{
		$this->longitude=$longitude;
	}


	/**
	 * 
	 * 
	 */
	public function getLatitude()
	{
		return $this->latitude;
	}

	/**
	 * 
	 * 
	 */
	public function setLatitude($latitude)
	{
		$this->latitude=$latitude;
	}

	/**
	 * 
	 * 
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * 
	 * 
	 */
	public function setValue($value)
	{
		$this->value=$value;
	}

	/**
	 * 
	 * 
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * All _ are repalced by space string to match with the type names of the data base (cf. site_mod.geographical_entity_type)
	 * 
	 */
	public function setType($type)
	{
		if(preg_match('#^(.*)?_?(.*)_name$#',$type))
		{
			$explodedType=explode('_',$type);
			if(count($explodedType)==3)
			{
				$this->type=$explodedType[0].' '.$explodedType[1];
			}else{
				$this->type=$explodedType[0];
			}
			
		}else{
			$this->type = $type;
		}
	}

	/**
	 * 
	 */
	public function getSon()
	{
		return $this->son;
	}

	/**
	 * 
	 */
	public function setSon($son)
	{
		$this->son=$son;
	}


	/**
	 * Check if the location name begins by the site name of the line 
	 */
	public function checkNamingLocation($siteObject)
	{
		$location=$this->getValue();
		$site=$siteObject->getValue();
		if(preg_match('#^(.*)_[0-9]$#',$location, $capture))
		{
			if($capture[1]==$site)
			{
				
			} else {
				throw new Exception("The location_name does not follow the correct naming convention.
				Please correct, re-save and try again.
				See the guidelines document for more information if needed. (Error log: 072)");
			}
		}
	}

	
	/**
	 * Check if the latitude coordinates follow one of the allowed formats and converts it to a unique format
	 */
	public static function CheckCoordinateLatitude($coordinate)
	{
		$defaultFrist=False;

		switch ($coordinate) 
		{
			case (preg_match('#^([0-9]){2,3}\*([0-9]){2}\*([0-9]){2}\.*([0-9])*\*[NS]$#', $coordinate) ? true : false) :
				break;

			case (preg_match('#^([0-9]){2,3}\*([0-9]){2}\.([0-9]){2,}\*[NS]$#', $coordinate) ? true : false) :
				$cardinal=substr($coordinate, -1);
				$explodedCoordinate=explode('*',$coordinate);
				$convertedCoordinate=Self::DMDecToDMS($explodedCoordinate[0],$explodedCoordinate[1]);
				$coordinate=$convertedCoordinate['deg'].'*'.$convertedCoordinate['min'].'*'.$convertedCoordinate['sec'].'*'.$cardinal;
				break;

			case (preg_match('#^([0-9]){2,3}\.([0-9]){2,}\*[NS]$#', $coordinate) ? true : false) :
				$cardinal=substr($coordinate, -1);
				$explodedCoordinate=explode('*',$coordinate);
				$convertedCoordinate=Self::DECtoDMS($explodedCoordinate[0]);
				$coordinate=$convertedCoordinate['deg'].'*'.$convertedCoordinate['min'].'*'.$convertedCoordinate['sec'].'*'.$cardinal;
				break;			

			default:
				$defaultFrist=True;			
				break;
		}

		if($defaultFrist)
		{
			$cardinal=substr($coordinate, -1);

			if($cardinal!=='N'&&$cardinal!=='S')
			{
				throw new Exception("The cardinal letter on the latitude coordinate is incorrect and/or missing.
				It must be N or S only. Please correct, re-save and re-insert. (Error log: 073)");				
			}

			$rest = substr($coordinate, 0, -1);

			$explodedCoordinate=apiPHP::multiexplode(array("’","|",":","*","-"," "),$rest);
			$parts=0;

			if($explodedCoordinate[0]>90)
			{
				throw new Exception("The value of the latitude coordinate is larger than the maximum value of 90.
				Please correct, re-save and re-insert. (Error log: 074)");
			}

			foreach ($explodedCoordinate as $key => $value) 
			{
				if(is_numeric($value))
				{
					$parts++;
				}
			}

			switch ($parts) 
			{
				case 1:
					$explodedCoordinate=Self::DECtoDMS($explodedCoordinate[0]);
					$coordinate=$explodedCoordinate['deg'].'*'.$explodedCoordinate['min'].'*'.$explodedCoordinate['sec'].'*'.$cardinal;
					break;

				case 2:
					$explodedCoordinate=Self::DMDecToDMS($explodedCoordinate[0],$explodedCoordinate[1]);
					$coordinate=$explodedCoordinate['deg'].'*'.$explodedCoordinate['min'].'*'.$explodedCoordinate['sec'].'*'.$cardinal;
					break;

				case 3:
					$coordinate=$explodedCoordinate[0].'*'.$explodedCoordinate[1].'*'.$explodedCoordinate[2].'*'.$cardinal;
					break;
				
				default:
					throw new Exception("The formatting of the latitude coordinate is incorrect. Please correct, re-save and re-insert.
					See the guidelines document for more information if needed. (Error log: 075)");
						
					break;
			}

			if(!preg_match('#^([0-9]){2,3}\*([0-9]){2}\*([0-9]){2}\.?([0-9])*\*[NS]$#', $coordinate))
			{
				throw new Exception("The formatting of the latitude coordinate is incorrect.
				Please correct, re-save and re-insert. See the guidelines document for more information if needed. (Error log: 076)");
			}


		} else {
			$split=explode('*', $coordinate);
			if($split[0]>90)
			{
				throw new Exception("The value of the latitude coordinate is larger than the maximum value of 90.
				Please correct, re-save and re-insert. (Error log: 077)");
			}
		}

		return $coordinate;
	}

	/**
	 * Check if the longitude coordinates follow one of the allowed formats and converts it to a unique format
	 */	
	public static function CheckCoordinateLongitude($coordinate)
	{
		$defaultFrist=False;
		switch ($coordinate) 
		{
			case (preg_match('#^([0-9]){2,3}\*([0-9]){2}\*([0-9]){2}\.?([0-9])*\*[EW]$#', $coordinate) ? true : false) :
				break;

			case (preg_match('#^([0-9]){2,3}\*([0-9]){2}\.([0-9]){2,}\*[EW]$#', $coordinate) ? true : false) :
				$cardinal=substr($coordinate, -1);
				$explodedCoordinate=explode('*',$coordinate);
				$convertedCoordinate=Self::DMDecToDMS($explodedCoordinate[0],$explodedCoordinate[1]);
				$coordinate=$convertedCoordinate['deg'].'*'.$convertedCoordinate['min'].'*'.$convertedCoordinate['sec'].'*'.$cardinal;
				break;

			case (preg_match('#^([0-9]){2,3}\.([0-9]){2,}\*[EW]$#', $coordinate) ? true : false) :
				$cardinal=substr($coordinate, -1);
				$explodedCoordinate=explode('*',$coordinate);
				$convertedCoordinate=Self::DECtoDMS($explodedCoordinate[0]);
				$coordinate=$convertedCoordinate['deg'].'*'.$convertedCoordinate['min'].'*'.$convertedCoordinate['sec'].'*'.$cardinal;
				break;			

			default:
				$defaultFrist=True;			
				break;
		}

		if($defaultFrist)
		{
			$cardinal=substr($coordinate, -1);
		
			if($cardinal!=='E'&&$cardinal!=='W')
			{
				throw new Exception("The cardinal letter on the longitude coordinate is incorrect and/or missing.
				It must be E or W only. Please correct, re-save and re-insert. (Error log: 078)");				
			}

			$rest = substr($coordinate, 0, -1);

			$explodedCoordinate=apiPHP::multiexplode(array("’","|",":","°","*","-"," "),$rest);

			$parts=0;

			if($explodedCoordinate[0]>180)
			{
				throw new Exception("The value of the longitude coordinate is larger than the maximum value of 180.
				Please correct, re-save and re-insert. (Error log: 079)");
			}

			foreach ($explodedCoordinate as $key => $value) 
			{
				if(is_numeric($value))
				{
					$parts++;
				}
			}

			switch ($parts) 
			{
				case 1:
					$explodedCoordinate=Self::DECtoDMS($explodedCoordinate[0]);
					$coordinate=$explodedCoordinate['deg'].'*'.$explodedCoordinate['min'].'*'.$explodedCoordinate['sec'].'*'.$cardinal;
					break;

				case 2:
					$explodedCoordinate=Self::DMDecToDMS($explodedCoordinate[0],$explodedCoordinate[1]);
					$coordinate=$explodedCoordinate['deg'].'*'.$explodedCoordinate['min'].'*'.$explodedCoordinate['sec'].'*'.$cardinal;
					break;

				case 3:
					$coordinate=$explodedCoordinate[0].'*'.$explodedCoordinate[1].'*'.$explodedCoordinate[2].'*'.$cardinal;
					break;
				
				default:
						throw new Exception("The formatting of the longitude coordinate is incorrect. Please correct, re-save and re-insert.
						See the guidelines document for more information if needed. (Error log: 080)");
						
					break;
			}

			if(!preg_match('#^([0-9]){2,3}\*([0-9]){2}\*([0-9]){2}\.?([0-9])*\*[EW]$#', $coordinate))
			{
				throw new Exception("The formatting of the longitude coordinate is incorrect. Please correct, re-save and re-insert.
				See the guidelines document for more information if needed. (Error log: 081)");
			}


		} else {
			$split=explode('*', $coordinate);
			if($split[0]>180)
			{
				throw new Exception("The value of the longitude coordinate is larger than the maximum value of 180.
				Please correct, re-save and re-insert. (Error log: 082)");
			}
		}
		return $coordinate;
	}

	/**
	 * Converts DMS ( Degrees / minutes / seconds ) to decimal format longitude / latitude
	 */
	private static function DMStoDEC($deg,$min,$sec)
	{
	    return $deg+((($min*60)+($sec))/3600);
	}    

	/**
	 * Converts decimal longitude / latitude to DMS ( Degrees / minutes / seconds 
	 */
	public static function DECtoDMS($dec)
	{
	    $vars = explode(".",$dec);
	    $deg = $vars[0];
	    $tempma = "0.".$vars[1];

	    $tempma = $tempma * 3600;
	    $min = floor($tempma / 60);
	    $sec = $tempma - ($min*60);

	    return array("deg"=>$deg,"min"=>$min,"sec"=>$sec);
	} 

	/**
	 * Converts decimal longitude / latitude to DMS ( Degrees / minutes / seconds )
	 */
	public static function DMDecToDMS($deg,$min)
	{
	    $vars = explode(".",$min);
	    $min = $vars[0];
	    $minDec = "0.".$vars[1];
	    $sec = $minDec * 60;

	    return array("deg"=>$deg,"min"=>$min,"sec"=>$sec);
	} 

}

?>