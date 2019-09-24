<?php
	class Sample
	{

		public $id_db;

		public $sample_name;
		public $sample_replicate_name;
		public $id_source;
		public $sampling;
		public $geo_entity;
		public $day_start;
		public $month_start;
		public $year_start;
		public $day_end;
		public $month_end;
		public $year_end;
		public $observation_type;
		public $sample_type;
		public $environment;
		public $array_mesure_sample;
		public $array_observation;
		private $logPath;

		function __construct($sample_name,$sampling,$geo_entity,$day_start,$month_start,$year_start,
			$day_end,$month_end,$year_end,$sample_type)
		{
			$this->sample_name=$sample_name;
			$this->sampling=$sampling;
			$this->geo_entity=$geo_entity;
			$this->day_start=$day_start;
			$this->month_start=$month_start;
			$this->year_start=$year_start;
			$this->day_end=$day_end;
			$this->month_end=$month_end;
			$this->year_end=$year_end;
			$this->sample_type=$sample_type;
		}
		
		public function setLog($logPath)
		{
			$this->logPath = $logPath;
		}
		
	
		public function get($var)
		{
			return $this->$var;
		}
	
		public function set($var,$value)
		{
			try
			{
				$value = $this->testType($var,$value);
				if($value!==false)
				{
					$this->$var = $value;
				}
				else
				{
					if($value!=='')
					{
						throw new Exception("Wrong type for $var value $value");
					}
				}
			}
			catch (Exception $e)
			{
				$this->$var = NULL;
				apiPHP::write_in_log($e->getMessage()."\n",$this->logPath);
				apiPHP::write_in_log($e->getTraceAsString()."\n",$this->logPath,FALSE);
				print($e->getMessage()."<br />");
			}
		}
	
		private function my_is_int($value)
		{
			$return = true;
			if(!is_int($value))
			{
				if(!preg_match("/^[0-9]+$/", $value))
				{
					$return = false;
				}
			}
			return($return);
		}
	
		private function testType($var,$value)
		{
			$return = false;
			try
			{
				switch ($var) {
					case "id_db":
						if(is_int($value))
						{
							$return = $value;
						};
						break;
					case "day_start":
						if($this->my_is_int($value))
						{
							$return = $value;
						};
						break;
					case "month_start":
						if($this->my_is_int($value))
						{
							$return = $value;
						};
						break;
					case "year_start":
						if($this->my_is_int($value))
						{
							$return = $value;
						};
						break;
					case "day_end":
						if($this->my_is_int($value))
						{
							$return = $value;
						};
						break;
					case "month_end":
						if($this->my_is_int($value))
						{
							$return = $value;
						};
						break;
					case "year_end":
						if($this->my_is_int($value))
						{
							$return = $value;
						};
						break;
					case "sample_name":
						if(is_string($value))
						{
							$return = $value;
						};
						break;
					case "observation_type":
						if(is_string($value))
						{
							$return = $value;
						};
						break;
					case "sample_replicate_name":
						if(is_string($value))
						{
							$return = $value;
						};
						break;
					case "sample_type":
						if(is_string($value))
						{
							$return = $value;
						};
						break;
					case "geo_entity":
						if(is_a($value,"GeoEntity"))
						{
							$return = $value;
						};
						break;
					case "sampling":
						if(is_a($value,"Sampling"))
						{
							$return = $value;
						};
						break;
					case "array_mesure_sample":
						if(is_a($value,"MeasureExp"))
						{
							$array = $this->array_mesure_sample;
							if(!is_array($array))
							{
								$array = array();
							}
							array_push($array, $value);
							$return = $array;
						}
						break;
					case "array_observation":
						if(is_a($value,"Observation"))
						{
							$array = $this->array_observation;
							if(!is_array($array))
							{
								$array = array();
							}
							array_push($array, $value);
							$return = $array;
						}
						break;
					default:
						throw new Exception("Variable $var unknown.");
					break;
				}
			}
			catch (Exception $e)
			{
				apiPHP::write_in_log($e->getMessage()."\n",$this->logPath);
				apiPHP::write_in_log($e->getTraceAsString()."\n",$this->logPath,FALSE);
				print($e->getMessage()."<br />");
			}
			return($return);
		}
	}
?>
