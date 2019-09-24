<?php


class apiPHP
{
	/**
	 * Search backwards starting from haystack length characters from the end
	 */
	public static function startsWith($haystack, $needle) 
	{
    	return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}

	/**
	 * Display an array content in a more readable way than print_r
	 */
	public static function print_rPlus($myArray)
	{
		echo '<pre>';
		print_r($myArray);
		echo '</pre>';
	}

	/**
	 * Explode a string chain according to more than one delimiter
	 */
	public static function multiexplode($delimiters,$string) 
	{
	    $ready = str_replace($delimiters, $delimiters[0], $string);
	    $launch = explode($delimiters[0], $ready);
	    return  $launch;
	}


	/* -- Content part -- */


	/**
	 * TO BE IMPLEMENTED
	 * 
	 * Find the climat of the geographical entity according the latitude and longitude
	 * 
	 */
	public static function FindClimat($latitude,$longitude)
	{
		//@TODO : A développer
		return 'test climat';
	}


	/**
	 * If a chemical measurement value is given its detection limit must be given too.
	 * For each chemicals in $arrayChemical the function check the presence of detection limit colunm and its value
	 */
	public static function checkChemicalDetectionLimit($arrayChemical,$arrayDetectionLimit)
	{
		foreach ($arrayChemical as $keyChemical => $valueChemical) 
		{
			if($valueChemical)
			{
				if(!$arrayDetectionLimit["DL_$keyChemical"])
				{
					throw new Exception("The detection limit for $keyChemical is missing.
					 Please correct, re-save and re-insert. (Error log: 038)");
				}
			}
		}
	}

	/**
	 * Check that on a same line there is no more than one kind of station
	 * 
	 */
	public static function checkStationConsistency($slicedLine)
	{

		if((($slicedLine['flow_station_name']|| $slicedLine['flow_station_code'])&&($slicedLine['rainfall_station_name']|| $slicedLine['rainfall_station_code']))
			||(($slicedLine['temperature_station_name'] || $slicedLine['temperature_station_code'])&&($slicedLine['flow_station_name']|| $slicedLine['flow_station_code']))
			||(($slicedLine['rainfall_station_name']|| $slicedLine['rainfall_station_code'])&&($slicedLine['temperature_station_name'] || $slicedLine['temperature_station_code']))
		)
		{
			throw new Exception("You have entered more than one type of station (flow, rainfall, temperature) on the same row.
			 Only one type of station is allowed per row.
			 Please correct, re-save and re-insert. (Error log: 039)");
		}
	}

	/**
	 * Print a warning when we have the outlier values 
	 * 
	 */
	public static function checkOutlier($type,$value,$fileType)
	{
		switch ($fileType) 
		{
			case 'site':
				$conf=Conf::$siteForks;
				break;
			
			case 'environment':
				$conf=Conf::$environmentForks;
				break;

			case 'fauna':
				$conf=Conf::$faunaForks;
				break;

			default:
				throw new Exception("The file type is incorrect. Please save your file as a tab-delimited .csv or .txt type, encoded in Unicode (UTF-8), then re-insert.
				 See the guidelines document for more information if needed. (Error log: 040) ", 1);
				break;
		}

		$min=($conf[$type]['max']-$conf[$type]['min'])*0.05+$conf[$type]['min'];

		$max=$conf[$type]['max']-($conf[$type]['max']-$conf[$type]['min'])*0.05;

		if($min!==NULL)
		{
			if($value < $min)
			{
				Self::print_rPlus("Warning: the value in the column $type is very low (near the minimum value allowed).
				Please check if the value is correct, if not reinsert the file with correct value (Error log: 041)");
			}	
		}
		if($max!==NULL)
		{
			if($value > $max)
			{
				Self::print_rPlus("Warning: the value on the column $type is very high (near the maximum value allowed).
				Please check if the value is correct, if not reinsert the file with correct value (Error log: 042)");	
			}
		}
	}

	/**
	 * @param $vocabulary array of allowed values with colunm name as key 
	 * 
	 */
	public static function checkVocabulary($vocabulary,$columnName,$value)
	{
		if($value&&$vocabulary[$columnName])
		{
			if(!in_array($value,$vocabulary[$columnName]))
			{
				throw new Exception("The value \"$value\" in column \"$columnName\" is outside the allowable range or vocabulary.
				Please correct, re-save and re-insert. See the Universe in the original template files for the list of vocabulary or range of values allowed in this field.
				(Error log: 043)");
			}
		}
	}

	/**
	 * Check if a value is in the range of allowed values
	 * 
	 */ 
	public static function checkInFork($type,$value,$fileType=NULL)
	{

		$conf=Conf::$measurementForks;

		
		$min=$conf[$type]['min'];
		$max=$conf[$type]['max'];


		if($min!==NULL)
		{
			if($value < $min)
			{
				throw new Exception("Value in type: '$type' value: '$value' is below the minimum allowable value. Please correct, re-save and re-insert.
				See the Universe in the original template files for the range of values allowed in this field. (Error log: 044)");
			}	
		}
		if($max!==NULL)
		{
			if($value > $max)
			{
				throw new Exception("Value in type: '$type' value: '$value' is above the maximum allowable value.
				Please correct, re-saveand re-insert. See the Universe in the original template files for the range of values allowed in this field. (Error log: 045)");
			}
		}
	}

	/**
	 * Tells if the measurements reffer to a station or a location
	 * 
	 */
	public static function locationOrStation($line)
	{
		if((!$line['lat_location'] || !$line['long_location'])&&(!$line['lat_station'] || !$line['long_station']))
		{
			throw new Exception("Error: at least one coordinate is missing in the latitude and/or longitude field for a location or station.
			Please correct, re-save and re-insert. (Error log: 046)");
		}

		if(($line['lat_location'] || $line['long_location']) && ($line['lat_station'] || $line['long_station']))
		{
			throw new Exception("Error: Location coordinates and station coordinates are both filled. 
			If you want this row to be for a location, delete the station coordinates. 
			If you want it to be for a station, enter a station name and delete the location coordinates. Please correct, re-save and re-insert.");
		}

		if (($line['lat_location'] && !$line['long_location']) || (!$line['lat_location'] && $line['long_location']))
		{
			throw new Exception("Something is wrong with the location coordinates on line n° $lineNumber.
			See the guidelines document for details on formatting coordinates.
			Please check, correct, re-save and re-insert. (Error log: 048)");
		} 
		else 
		{
			if (!$line['lat_location'] && !$line['long_location'])
			{
				if (($line['lat_station'] && !$line['long_station']) || (!$line['lat_station'] && $line['long_station']))
				{
					throw new Exception("Something is wrong with the station (flow, rainfall or temperature) coordinates on line n° $lineNumber.
					See the guidelines document for details on formatting coordinates.
					Please check, correct, re-save and re-insert. (Error log: 049)");
				} 
				else
				{
					return 'station';
				}
			}
			return 'location';
		}
	}


	/**
	 * Check that we have coordinate values for station when a station related column is filled 
	 * 
	 */
	public static function CheckMandatoryStationCoordinate($geoEntityPart,$coordinatePart)
    {
    	foreach ($geoEntityPart as $key => $value) 
    	{
    		if(in_array($key,array('flow_station_name','rainfall_station_name','temperature_station_name','flow_station_code','rainfall_station_code','temperature_station_code')))
    		{
    			if($value)
    			{
					if(!isset($coordinatePart['lat_station']) || !isset($coordinatePart['long_station']))
			        {
			            throw new Exception("You have provided a station name, therefore the station’s latitude and longitude must be provided.
			            Please enter the station’s coordinates under the columns lat_station and long_station, re-save and re-insert. (Error log: 050)");
			        }
    			}    			
    		}
    	}
    }



	/* -- End Content part -- */

    /* -- Header part -- */


    /**
     * Check if mandatory column are in the given file. Value below the column may be left blank.
     */
    public static function checkMandatoryColumn($headerLine,$fileType)
    {
    	$name=strtolower($fileType).'MandatoryColumn';
    	$mandatoryColumnArray=Conf::$$name;
    	foreach ($mandatoryColumnArray as $column)
    	{
    		if(!in_array($column,$headerLine))
    		{
    			throw new Exception("The column '$column' is missing");
    		}
    	}
    }

	/**
	 * Check that column depending on a timespan is followed by a corresponding timespan column
	 * 
	 * @var Table of time dependant measurement
	 */
	public static function CheckTimespanOrder($timeDependantMeasurement)
	{
		$flagTimeSpan=False;
		foreach ($timeDependantMeasurement as $key => $value) 
		{
			if(preg_match('#^discrete_.*_period_timespan$#',$value))
			{

			} else {
				if($value=='geoclass'||$value=='intermittency_origin')
				{
					$flagTimeSpan=False;
				} else {
					if(!$flagTimeSpan)
					{
						$flagTimeSpan = True;
					} else {
						if(preg_match('#timespan$#',$value))
						{
							$flagTimeSpan = False;
						} else {
							throw new Exception("Error: the order of the headers in the data matrix area of the file is incorrect.
							The order must match that in the original template file.
							Please correct, re-save and re-insert. (Error log: 051)");
						} 
					}
				}
			}
		}
	}

	/**
	 * Checks that mandatory geo entities colunms have value
	 */
	public static function CheckMandatoryGeoEntity($geo)
	{
		$mandatory=array('site_name','location_name','river_name','catchment_name','country');
		foreach ($mandatory as $key => $value) 
		{
			if(!in_array($value, $geo))
			{
				throw new Exception("The column $value is mandatory but no value has been provided here.
				 Please correct, re-save and re-insert. (Error log: 052)");
			}
		}		
	}

	/**
	 * Check the order of column from the csv site file 
	 * 
	 * @var headerLine
	 */
	public static function CheckSiteHearderOrder($headerLine)
	{
		$order= array('site_name',
		'location_name',
		'river_name',
		'catchment_name',
		'country',
		'lat_location',
		'long_location',
		'flow_regime',
		'flow_regime_timespan');

		$i=0;

		foreach ($headerLine as $key => $value) 
		{
			if(in_array($value,$order))
			{
				if($value==$order[$i])
				{
					++$i;				
				}else{
					throw new Exception("Error: the order of the headers in the data matrix area of the file is incorrect. The order must match that in the original template file.
					Please correct, re-save and re-insert. (Error log: 053)");
				}				
			}
		}
	}

	/**
	 * Check the order of column from the csv environment file 
	 * 
	 * @var headerLine
	 */
	public static function CheckEnvironmentHeaderOrder($headerLine)
	{
		$order=Conf::$environmentHeaderOrder;

		$i=0;

		foreach ($headerLine as $key => $value) 
		{
			if(in_array($value,$order))
			{
				if($value==$order[$i])
				{
					++$i;				
				}else{
					throw new Exception("Error: the order of the headers in the data matrix area of the file is incorrect.
					 The order must match that in the original template file.
					 Please correct, re-save and re-insert. (Error log: 054)");
				}				
			}
		}
	}

	/**
	 * check the presence of a given column from csv file
	 */
	public static function IsColumnPresent($columnName,$headerLine)
	{
		foreach ($headerLine as $value) {
			if($value == $columnName){
				return True;
			}
		}
		return False;
	}

	/**
	 * Split the header in different part depending on boundaries.
	 * 
	 * @var $headerLine: Header to be classified 
	 * @var $boundary: Array of boundaries, the array order respect the order of boundaries in the Header
	 */
	public static function classifyHeader($headerLine,$boundary)
	{	
		$arrayHeader= array();

		$firstK = '';
		$secondK = '';

		for ($i=0; $i < count($boundary) ; $i++)
		{
			if($boundary[$i])
			{
				switch ($boundary[$i]['begining']) 
				{
					case '?':
						$firstK=array_keys($headerLine,$boundary[$i-1]['end'])[0]+1;
						break;

					default:
						$firstK=array_keys($headerLine,$boundary[$i]['begining'])[0];
						break;
				}

				switch ($boundary[$i]['end']) 
				{
					case '?':
					//On reagarde si on est sur le dernier groupe 
						if($i+1>=count($boundary))
						{
							$secondK=count($headerLine);
						}else{
							$secondK=array_keys($headerLine,$boundary[$i+1]['begining'])[0];
						}
						break;

					default:
						$secondK=array_keys($headerLine,$boundary[$i]['end'])[0]+1;
						break;
				}
				$arrayHeader[$i]= array_slice($headerLine,$firstK,$secondK-$firstK);
			}else{
				$arrayHeader[$i]= array('','');
			}
		}

		$lineNumber=0;
		foreach ($arrayHeader as $key => $value) 
		{
			foreach ($value as $k => $v)
			{
				$arrayHeaderBis[$key][$lineNumber]=$v;
				$lineNumber=$lineNumber+1;				
			}
		}
		return $arrayHeaderBis;
	}


	/**
	 * Filter 1: It has to match with the format 'xxxx-xxxx' plus eventually '$frequency'. 
	 * Frequency is any word from the universe controlled vocabulary.
	 * 
	 * @var Column name to be checked
	 */
    public static function checkTimespanFormat($value, $nom) 
    {
    	if($value)
    	{
    		if(preg_match('#^(0[1-9]|[1-2][0-9]|3[0-1])?/?(0[1-9]|1[0-2])?/?[0-9]{4}$#',$value))
	    	{
	    		
	    	}else{
	    		throw new Exception("The formatting of the timespan value at: $nom is incorrect. Please correct, re-save and re-insert.
	    		Please follow the formatting as advised under Naming Convention in the original template file. (Error log: 055)");
	    		
	    	}
    	} else {
    		throw new Exception("A mandatory timespan value is missing at: $nom. 
    		Please correct, re-save and re-insert. (Error log: 056)");
    	}
	    	
    }

    /**
     * Check if a frequency is in the controlled vocabulary
     * 
     */
    public static function checkFrequency($frequency, $nom="--no column name given--")
    {
    	if($frequency)
    	{
	    	if(!in_array($frequency, array("15 minute", "hourly", "daily", "monthly", "quarterly")))
		    {
		        throw new Exception("The timestep qualifier (after $ in the timespan field at $nom) is outside the allowable vocabulary
		        (use 15minute, hourly, daily, monthly, or quarterly only). Please correct, re-save and re-insert. (Error log: 057)");
		    }else{

		    }	
    	}

    }

    /**
     * 
     */
    public static function checkDiscretTimespanFormat($date)
    {
    	if(preg_match('#^(0[1-9]|[1-2][0-9]|3[0-1])?/?(0[1-9]|1[0-2])?/?[0-9]{4}$#', $date))
    	{

    	} else {
    		throw new Exception("The formatting of the timespan value for the dicrete_ZF_period and/or discrete_dry_period fields is incorrect. Please correct, re-save and re-insert.
    		 Please follow the formatting as advised under Naming Convention in the original template file. (Error log: 058)");
    	}
    }


    /**
     * Split one date string into a day string, a month string and a year string 
     * 
     */
    public static function manageDate($date,$columnName)
    {
    	$arrayDate=array();

    		if(preg_match('#^(0[1-9]|[1-2][0-9]|3[0-1])/(0[1-9]|1[0-2])/([0-9]{4})$#',$date,$capture))
	    	{
	    		$arrayDate['day']=$capture[1];
	    		$arrayDate['month']=$capture[2];
	    		$arrayDate['year']=$capture[3];
	    	} else {
	    		if(preg_match('#^(0[1-9]|1[0-2])/([0-9]{4})$#',$date,$capture))
	    		{
	    			$arrayDate['month']=$capture[1];
	    			$arrayDate['year']=$capture[2];
	    		} else {
	    			if(preg_match('#^([0-9]{4})$#',$date,$capture))
	    			{
	    				$arrayDate['year']=$capture[1];
	    			} else {
	    				throw new Exception("The date format is incorrect at column \"$columnName\".
	    				Please correct, re-save and re-insert. You must use the dd/mm/yyyy format. ");
	    			}
	    		}
	    	}
    	return $arrayDate;
    }

    /* -- End Header part -- */

	/**
	* write in log file with facultative datation
	* 
	*/
	public static function write_in_log($query,$dir_log="/home/mancini/Code/www/logs_IRBAS",$time=TRUE)
	{
		try
		{
			if(!is_null($dir_log))
			{
				$today = date("m_d_y");
				$log = $dir_log."_".$today.".txt";
				if($fp=fopen($log, "a"))
				{
					if($time)
					{
						$time = @date('[d/M/Y:H:i:s]');
						fwrite($fp,$time." : ".$query);
					}
					else
					{
						fwrite($fp,$query);
					}
					fclose($fp);
				}
				else
				{
					throw new Exception("Unexpected error occurred during file management.
					Please contact the web-interface administrator and quote error message:
					 Impossible to write in file ".$dir_log."_".$today.".txt (Error log: 060)");
				}
			}
			else
			{
			//	throw new Exception("No repertory initialised");
			}
		}
		catch (Exception $e)
		{
			print($e->getMessage()."<br />");
		}
	}
	
	public static function trim_value(&$value)
	{
		$value = trim($value);
	}
	
	public static function in_array_i($needle,$haystack)
	{
		return in_array(strtolower(trim($needle)),$haystack);
	}
	
	public static function checkDate($year,$month=NULL,$day=NULL)
	{
		if(is_null($month))
		{
			$month = 1;
		}
		if(is_null($day))
		{
			$day = 1;
		}
		if(preg_match("/^[0-9]+$/",$year) && preg_match("/^[0-9]{1,2}$/",$month) && preg_match("/^[0-9]{1,2}$/",$day))
		{
			return checkdate($month,$day,$year);
		}
		return false;
	}
	
	public static function my_is_null($value)
	{
		$return = true;
		if(!is_null($value)&&$value!=''&&$value!='{}')
		{
			$return = false;
		}
		return($return);
	}

	public static function my_is_int($value)
	{
		$return = true;
		if(!is_int($value))
		{
			if(!preg_match("/^-?[0-9]+$/", $value))
			{
				$return = false;
			}
		}
		return($return);
	}
	
	public static function objectToArray($d)
	{
		if(is_object($d))
		{
			// Gets the properties of the given object
			// with get_object_vars function
			$d = get_object_vars($d);
		}
		if(is_array($d))
		{
			/*
			 * Return array converted to object
			* Using __FUNCTION__ (Magic constant)
			* for recursive call
			*/
			return array_map(__METHOD__, $d);
		}
		else
		{
			// Return array
			return $d;
		}
	}

}
?>
