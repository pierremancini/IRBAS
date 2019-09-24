<?php
	class Observation
	{
		public $id_db;
		public $taxon;
		public $sample;
		public $type_observation;
		public $value;
		private $logPath;

		public function __construct($id_db,$taxon,$sample,$type_observation,$value,$logPath=NULL)
		{
			$this->id_db=$id_db;
			$this->taxon=$taxon;
			$this->sample=$sample;
			$this->type_observation=$type_observation;

			if($taxon)
			{
				if($value<0)
				{
					throw new Exception("Error: Abundance of '$taxon' has negative value : '$value'");	
				}
			}
			$this->value=$value;
			$this->logPath=$logPath;
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
					case "id_db":
						if(is_int($value))
						{
							$return = $value;
						};
						break;
					case "sample":
						if(is_a($value,"Sample"))
						{
							$return = $value;
						};
						break;
					case "taxon":
						if(is_string($value))
						{
							$return = $value;
						};
						break;
					case "type_observation":
						if(is_string($value))
						{
							$return = $value;
						};
						break;
					case "value":
						if(is_numeric($value))
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
