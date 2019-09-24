<?php

	class Taxo
	{
		public $nom;
		public $rang;
		public $auteur;
		public $id_interne;
		public $id_parent;
		public $id_true_taxon;
		public $source;
		
		public function __construct($nom,$rang,$auteur=NULL,$id_interne=NULL,
			$id_parent=NULL,$id_true_taxon=NULL,$source=NULL)
		{
			$this->nom=$nom;
			$this->rang=$rang;
			$this->auteur=$auteur;
			$this->id_interne=$id_interne;
			$this->id_parent=$id_parent;
			$this->id_true_taxon=$id_true_taxon;
			$this->source=$source;
		}
		
		public function get($var)
		{
			return $this->$var;
		}
		
		public function set($var,$value)
		{
			try
			{
				if($this->testType($var,$value))
				{
					$this->$var = $value;
				}
				else
				{
					if($value !== "")
					{
						throw new Exception("Mauvais type pour $var valeur $value");
					}
				}
			}
			catch (Exception $e)
			{
				print($e->getMessage()."<br />");
			}
		}
		
		private function my_is_internal_id($value)
		{
			$return = true;
			if(!is_int($value))
			{
				if(!preg_match("/^[0-9]+$/", $value))
				{
					if(!preg_match("/^[a-zA-Z0-9]+_[0-9]+$/", $value))
					{
						$return = false;
					}
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
					case "id_interne":
						if($this->my_is_internal_id($value))
						{
							$return = true;
						};
					break;
					case "id_parent":
						if($this->my_is_internal_id($value))
						{
							$return = true;
						};
					break;
					case "id_true_taxon":
						if($this->my_is_internal_id($value))
						{
							$return = true;
						};
					break;
					case "rang":
						if(is_string($value))
						{
							$return = true;
						};
					break;
					case "auteur":
						if(is_string($value))
						{
							$return = true;
						};
					break;
					case "nom":
						if(is_string($value))
						{
							$return = true;
						};
					break;
					case "source":
						if(is_string($value))
						{
							$return = true;
						};
					break;
					default:
						throw new Exception("Variable $var inconnue.");
					break;
				}
			}
			catch (Exception $e)
			{
				print($e->getMessage()."<br />");
			}
			return($return);
		}
	}

?>