<?php
	class Sampling
	{
		/**
		 * Return code after insertion of Sampling
		 */
		public $id_db;
		
		public $name_sampling;
		public $protocol;
		public $strategy;
		public $array_sample;
		public $array_parameter;
		private $logPath;

		function __construct($name_sampling,$protocol,$strategy,$array_sample,$array_parameter)
		{
			$this->id_db=$id_db;
			$this->name_sampling=$name_sampling;
			$this->protocol=$protocol;
			$this->strategy=$strategy;
			$this->array_sample=$array_sample;
			$this->array_parameter=$array_parameter;
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
	
		private function testType($var,$value)
		{
			$return = false;
			try
			{
				switch ($var) {
					case "array_parameter":
						if(is_a($value,"MeasureExp"))
						{
							$array = $this->array_parameter;
							if(!is_array($array))
							{
								$array = array();
							}
							array_push($array, $value);
							$return = $array;
						}
						break;
					case "array_sample":
						if(is_a($value,"Sample"))
						{
							$array = $this->array_sample;
							if(!is_array($array))
							{
								$array = array();
							}
							array_push($array, $value);
							$return = $array;
						}
						break;
					case "name_sampling":
						if(is_string($value))
						{
							$return = $value;
						};
						break;
					case "protocol":
						if(is_string($value))
						{
							$return = $value;
						};
						break;
					case "strategy":
						if(is_string($value))
						{
							$return = $value;
						};
						break;
					case "id_db":
						if(is_int($value))
						{
							$return = $value;
						};
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
