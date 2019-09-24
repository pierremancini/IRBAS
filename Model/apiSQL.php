<?php
//TODO replace ILIKE by UPPER(x)=UPPER(y) + index in SQL functions for case insensitive equality (performance)
class apiSQL
{
	private $dbh;
	private $debug;
	private $typeInsert;
	private $arrayQueryPrepared;
	private $transactionInCourse;
	private $logPath;
	
	//prepared queries
	private $querySourcePrepared;
	private $queryNameSourcePrepared;
	private $queryReferencePrepared;
	private $queryReferenceTaxoPrepared;
	private $queryAuthorPrepared;
	private $queryAuthorTaxoPrepared;
	private $queryTypePrepared;
	private $queryTaxonomyPrepared;
	private $querySynonymPrepared;
	private $queryBelongPrepared;
	private $querySpeciesPrepared;
	private $queryTaxonSupPrepared;

	private $insertSourcePrepared;
	private $insertNameSourcePrepared;
	private $insertAuthorPrepared;
	private $insertAuthorTaxoPrepared;
	private $insertTypePrepared;
	private $insertTaxonomyPrepared;
	private $insertSynonymPrepared;
	private $insertBelongPrepared;
	private $insertReferencePrepared;


	private $queryMethodPrepared;
	private $queryRelevePrepared;
	private $queryLocPrepared;
	private $queryPubPrepared;
	private $queryPubRelPrepared;
	private $querySitePrepared;
	private $queryPlotPrepared;
	private $queryNameParameterPrepared;
	private $queryAbundPrepared;

	private $insertRelevePrepared;
	private $insertLocPrepared;
	private $insertPubPrepared;
	private $insertPubRelPrepared;
	private $insertSitePrepared;
	private $insertPlotPrepared;
	private $insertReleveParameterPrepared;
	private $insertReleveNameParameterPrepared;
	private $insertAbundPrepared;

	/*
	 ****************             ***************
	**************** FONCTION PDO ***************
	****************              ***************
	*/

	/**
	 *
	 * Constructor
	 * @param $host
	 * @param $port
	 * @param $dbname
	 * @param $user
	 * @param $pass
	 * @param $prepare
	 * @return Ambigous <NULL, PDO>
	 */
	function __construct($host,$port,$dbname,$user,$pass,$prepare=null)
	{
		$this->setDebug(false);
		//insertion mode
		$this->settypeInsert('functionSQL');
		$this->transactionInCourse = 0;
		try
		{
			$this->dbh = new PDO("pgsql:host=$host port=$port dbname=$dbname", "$user", "$pass");
			//print postgres Exception
			$this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			if($this->typeInsert=='preparePDO')
			{
				$this->prepareQuery($prepare);
			}
		}
		catch(PDOException $e)
		{
			apiPHP::write_in_log($e->getMessage()."\n",$this->logPath);
			apiPHP::write_in_log($e->getTraceAsString()."\n",$this->logPath,FALSE);
			print("Error : ".$e->getMessage()."<br/>");
		}
		return($this->dbh);
	}

	/**
	 * 
	 * DEPRECATED Prepare query depending of module
	 * @param string $arg Module to prepare query
	 */
	function prepareQuery($arg=null)
	{
		$querySource = "SELECT id_source AS id_source ".
									"FROM reference_mod.source ".
									"JOIN reference_mod.name_source USING(id_name_source) ".
									"WHERE name_source=? ".
										"AND source_ref=?;";
		$this->querySourcePrepared = $this->dbh->prepare($querySource);
		$queryNameSource = "SELECT id_name_source AS id_name_source ".
									"FROM reference_mod.name_source ".
									"WHERE name_source=? ;";
		$this->queryNameSourcePrepared = $this->dbh->prepare($queryNameSource);
		$queryReference = "SELECT id_object AS id_releve ".
									"FROM reference_mod.reference ".
									"WHERE id_source=? AND id_object=? AND table_ref=?;";
		$this->queryReferencePrepared = $this->dbh->prepare($queryReference);
		$queryAuthor = "SELECT id_auteur AS id_author ".
									"FROM releve.auteur ".
									"WHERE nom_auteur=?;";
		$this->queryAuthorPrepared = $this->dbh->prepare($queryAuthor);
		$querySpecies = "SELECT id_taxonomy AS id_taxon ".
									"FROM taxonomy_mod.taxonomy ".
									"WHERE name_taxonomy=?;";
		$this->querySpeciesPrepared = $this->dbh->prepare($querySpecies);

		$insertSource = "INSERT INTO reference_mod.source (id_source, source_ref) ".
									"VALUES (?,?);";
		$this->insertSourcePrepared = $this->dbh->prepare($insertSource);
		$insertNameSource = "INSERT INTO reference_mod.name_source (name_source) ".
									"VALUES (?);";
		$this->insertNameSourcePrepared = $this->dbh->prepare($insertNameSource);
		$insertAuthor = "INSERT INTO releve.auteur (nom_auteur) ".
									"VALUES (?);";
		$this->insertAuthorPrepared = $this->dbh->prepare($insertAuthor);
		$queryAuthorTaxo = "SELECT id_author_taxonomy AS id_author ".
												"FROM taxonomy_mod.author_taxonomy ".
												"WHERE name_author=?;";
		$this->queryAuthorTaxoPrepared = $this->dbh->prepare($queryAuthorTaxo);
		$insertAuthorTaxo = "INSERT INTO taxonomy_mod.author_taxonomy (name_author) ".
									"VALUES (?);";
		$this->insertAuthorTaxoPrepared = $this->dbh->prepare($insertAuthorTaxo);
		$insertReference = "INSERT INTO reference_mod.reference (id_source, id_object, table_ref) ".
							 		"VALUES (?,?,?);";
		$this->insertReferencePrepared = $this->dbh->prepare($insertReference);

		switch($arg)
		{
			case 'taxo':
				$queryType = "SELECT id_type AS id_type ".
											"FROM taxonomy_mod.type_taxonomy ".
											"WHERE name_type=?;";
				$this->queryTypePrepared = $this->dbh->prepare($queryType);
				$queryTaxonomy = "SELECT id_taxonomy AS id_taxon ".
												"FROM taxonomy_mod.taxonomy ".
												"WHERE id_type=? ".
													"AND name_complete_taxonomy=?;";
				$this->queryTaxonomyPrepared = $this->dbh->prepare($queryTaxonomy);
				$queryReferenceTaxo = "SELECT id_object AS id_taxon ".
													"FROM reference_mod.reference ".
													"WHERE id_source=?;";
				$this->queryReferenceTaxoPrepared = $this->dbh->prepare($queryReferenceTaxo);
				$querySynonym = "SELECT * FROM taxonomy_mod.synonym ".
													"WHERE id_taxonomy_true=? AND id_taxonomy_syn=?;";
				$this->querySynonymPrepared = $this->dbh->prepare($querySynonym);
				$queryBelong = "SELECT * FROM taxonomy_mod.belong_to ".
													"WHERE id_taxonomy_father=? AND id_taxonomy_son=?;";
				$this->queryBelongPrepared = $this->dbh->prepare($queryBelong);
					

				$insertType = "INSERT INTO taxonomy_mod.type_taxonomy (name_type) ".
											"VALUES (?);";
				$this->insertTypePrepared = $this->dbh->prepare($insertType);
				$insertTaxonomy = "INSERT INTO taxonomy_mod.taxonomy (id_author_taxonomy, id_type, name_taxonomy, name_complete_taxonomy) ".
											"VALUES (?,?,?,?);";
				$this->insertTaxonomyPrepared = $this->dbh->prepare($insertTaxonomy);
				$insertSynonym = "INSERT INTO taxonomy_mod.synonym (id_taxonomy_true,id_taxonomy_syn) ".
												"VALUES (?,?);";
				$this->insertSynonymPrepared = $this->dbh->prepare($insertSynonym);
				$insertBelong = "INSERT INTO taxonomy_mod.belong_to (id_taxonomy_father, id_taxonomy_son) ".
												"VALUES (?,?);";
				$this->insertBelongPrepared = $this->dbh->prepare($insertBelong);
				;
				break;
			case 'releve':
				$queryMethod = "SELECT id_methode AS id_method ".
												"FROM releve.method ".
												"WHERE id_type_methode=?;";
				$this->queryMethodPrepared = $this->dbh->prepare($queryMethod);
				$queryReleve = "SELECT id_releve AS id_releve ".
												"FROM releve.releve JOIN reference_mod.reference ON(id_object=id_releve) ".
												"WHERE id_source=? ".
													"AND jour_releve=? AND mois_releve=? AND annee_releve=?;";
				$this->queryRelevePrepared = $this->dbh->prepare($queryReleve);
				$queryLoc = "SELECT id_coordonnees AS id_loc, altitude AS altitude, slope AS slop, aspect AS aspect ".
												"FROM releve.coordonnees ".
												"WHERE x=? AND y=? AND precision_loc=?;";
				$this->queryLocPrepared = $this->dbh->prepare($queryLoc);
				$queryPub = "SELECT id_publication AS id_pub ".
  												"FROM publication.publication ".
												"WHERE nom_publication=?;";
				$this->queryPubPrepared = $this->dbh->prepare($queryPub);
				$queryPubRel = "SELECT id_pub AS id_pub ".
  												"FROM publication.publication_relationship ".
												"WHERE id_pub=? AND id_pub_included=?;";
				$this->queryPubRelPrepared = $this->dbh->prepare($queryPubRel);
				$querySite = "SELECT id_site AS id_site ".
  												"FROM releve.site ".
												"WHERE nom_site=?;";
				$this->querySitePrepared = $this->dbh->prepare($querySite);
				$queryPlot = "SELECT id_plot AS id_plot ".
												"FROM releve.plot ".
												"WHERE nom_plot=? AND id_site=?;";
				$this->queryPlotPrepared = $this->dbh->prepare($queryPlot);
				$queryNameParameter = "SELECT id_nom_parametre AS id_column ".
												"FROM releve.nom_parametre ".
												"WHERE nom_colonne=?;";
				$this->queryNameParameterPrepared = $this->dbh->prepare($queryNameParameter);
				$queryAbund = "SELECT id_abondance AS id_abund ".
												"FROM releve.abondance ".
												"WHERE id_releve=? AND id_species=?;";
				$this->queryAbundPrepared = $this->dbh->prepare($queryAbund);

				$insertReleve = "INSERT INTO releve.releve (id_plot, id_methode, id_auteur, jour_releve, mois_releve, annee_releve, ".
										"type_releve, id_coordonnees, id_publication, estimated_date,unite_abondance) ".
									"VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
				$this->insertRelevePrepared = $this->dbh->prepare($insertReleve);
				$insertLoc = "INSERT INTO releve.coordonnees (x, y, precision_loc, slope, altitude, aspect) ".
									"VALUES (?, ?, ?, ?, ?, ?);";
				$this->insertLocPrepared = $this->dbh->prepare($insertLoc);
				$insertPub = "INSERT INTO publication.publication(nom_publication, type_publication) ".
    								"VALUES (?, ?);";
				$this->insertPubPrepared = $this->dbh->prepare($insertPub);
				$insertPubRel = "INSERT INTO publication.publication_relationship(id_pub, id_pub_included) ".
    								"VALUES (?, ?);";
				$this->insertPubRelPrepared = $this->dbh->prepare($insertPubRel);
				$insertSite = "INSERT INTO releve.site(nom_site, description_site) ".
    								"VALUES (?, ?);";
				$this->insertSitePrepared = $this->dbh->prepare($insertSite);
				$insertPlot = "INSERT INTO releve.plot(nom_plot, id_site, description_plot) ".
    								"VALUES (?, ?, ?);";
				$this->insertPlotPrepared = $this->dbh->prepare($insertPlot);
				$insertReleveNameParameter = "INSERT INTO releve.nom_parametre(nom_colonne,definition_colonne) ".
									"VALUES (?, ?);";
				$this->insertReleveNameParameterPrepared = $this->dbh->prepare($insertReleveNameParameter);
				$insertReleveParameter = "INSERT INTO releve.parametre(id_releve, id_nom_parametre, valeur_parametre, type_sql_valeur, unite_parametre) ".
									"VALUES (?, ?, ?, ?, ?);";
				$this->insertReleveParameterPrepared = $this->dbh->prepare($insertReleveParameter);
				$insertAbund = "INSERT INTO releve.abondance(id_releve, valeur, id_species) ".
									"VALUES (?, ?, ?);";
				$this->insertAbundPrepared = $this->dbh->prepare($insertAbund);
				;
				break;
			default:
				;
				break;
		}
			
	}

	/**
	 * 
	 * Set log path
	 * TODO Test if it is a writable path
	 * @param string $logPath
	 */
	public function setLog($logPath)
	{
		$this->logPath = $logPath;
	}
	
	/**
	 * 
	 * Track exception
	 * @param Exception $e
	 */
	function trackException($e)
	{
		if(is_a($e,"Exception"))
		{
			apiPHP::write_in_log($e->getMessage()."\n",$this->logPath);
			apiPHP::write_in_log("File: ".$e->getFile()." ; Line ".$e->getLine()." : ".$e->getTraceAsString()."\n",$this->logPath,FALSE);
			print("<br />".$e->getMessage()."<br />");
			if($this->debug)
			{
				if($this->debug>1)
				{
					print("<br />File: ".$e->getFile()." ; Line ".$e->getLine()." : ".$e->getTraceAsString()."<br />");
				}
			}
			die;
		}
		else
		{
			print("Problem tracking exception");
		}
	}
	
	/**
	 *
	 * Debug sql depending on a level
	 * @param int/bool $bool
	 */
	function setDebug($bool)
	{
		if($bool===0 || is_null($bool))
		{
			$this->debug = 0;
		}
		else
		{
			if(is_int($bool)&&$bool>1)
			{
				$this->debug = $bool;
			}
			else
			{
				$this->debug = 1;
			}
		}
	}

	/**
	 * 
	 */
	function getDebug()
	{
		return $this->debug;
	}

	/**
	 *
	 * Choose insertion type. Either prepare/execute in PDO, or by classical query with SQL functions
	 * @param string $type : preparePDO, functionSQL (default), (getPDO?)
	 */
	function setTypeInsert($type)
	{
		if(is_null($type)||!is_string($type))
		{
			$this->typeInsert = 'functionSQL';
		}
		else
		{
			$this->typeInsert = $type;
		}
	}


	/**
	 *
	 * Destructor
	 */
	function __destruct()
	{
		$this->dbh = NULL;
	}

	/**
	 *
	 * Execute query and return it in an hash table
	 * @param string $query
	 */
	function get($query)
	{
		$return = NULL;
		try
		{
			//check db handle
			if(!is_null($this->dbh))
			{
				//debug
				if($this->debug>1)
				{
					apiPHP::write_in_log($query."\n",$this->logPath);
				}
				if($queryStatement=$this->dbh->query($query))
				{
					$result = $queryStatement->fetchAll(PDO::FETCH_ASSOC);
					$return = $result;
				}
				else
				{
					throw(new Exception("Query $query failed."));
				}
			}
			else
			{
				throw(new Exception("Connection to database not initialized."));
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
			if($this->transactionInCourse)
			{
				$this->dbh->rollback();
			}
			die("<h2>Critical error in database : if you were in a transactionnal insert script, nothing would be insered.</h2>");
		}
		return($return);
	}

	/**
	 * 
	 * Execute prepared query and return it in an hash table
	 * @param PreparedStatement $queryPrepared Query prepared
	 * @param array $arrayParam Array containing parameters if any
	 * @param boolean $isInsert If it an insertion or not
	 * @throws Exception
	 */
	function exec($queryPrepared,$arrayParam,$isInsert=false)
	{
		$return = NULL;
		$stringParameter = null;
		try
		{
			//test if array is not empty
			if(count($arrayParam)==0)
			{
				throw new Exception("Array of parameters is empty");
			}
			//check preparation of the query
			if(is_a($queryPrepared, 'PDOStatement'))
			{
				//debug
				if($this->debug>1)
				{
					print($queryPrepared->queryString."\n");
				}
				if($result=$queryPrepared->execute($arrayParam))
				{
					if(!$isInsert)
					{
						$result = $queryPrepared->fetchAll(PDO::FETCH_ASSOC);
					}
					$return = $result;
				}
				else
				{
					apiPHP::write_in_log($queryPrepared->errorInfo()."\n",$this->logPath);
					apiPHP::write_in_log($queryPrepared->queryString."\n",$this->logPath,FALSE);
					if($this->debug)
					{
						print_r($queryPrepared->errorInfo());
						$query = $queryPrepared->queryString;
						if($this->debug>2)
						{
							var_dump($arrayParam);
							print_r($arrayParam);
						}
					}
					throw(new Exception("Query $query failed."));
				}
			}
			else
			{
				throw(new Exception("Query No prepared."));
			}
		}
		catch(Exception $e)
		{
			if($this->debug)
			{
				if($this->typeInsert=='preparePDO')
				{
					print_r($queryPrepared->errorInfo());
				}
				print_r($this->dbh->errorInfo());
				print($e->getMessage()."\n");
			}
			if($this->typeInsert=='preparePDO')
			{
				apiPHP::write_in_log($queryPrepared->errorInfo()."\n",$this->logPath);
			}
			apiPHP::write_in_log($e->getMessage()."\n",$this->logPath,FALSE);
		}
		return($return);
	}

	/**
	 * 
	 * Start a transaction if any in course, else close it (commit or rollback)
	 * Use it directly at the script beginning and ending for a block insertion
	 * @param boolean $problem Rollback instead of commit
	 */
	function transaction($problem=FALSE)
	{
		try
		{
			if(!$problem)
			{
				if(!$this->transactionInCourse)
				{
					$this->dbh->beginTransaction();
					$this->transactionInCourse = 1;
				}
				else
				{
					$this->dbh->commit();
					$this->transactionInCourse = 0;
				}
			}
			else
			{
				if($this->transactionInCourse)
				{
					$this->dbh->rollBack();
					$this->transactionInCourse = 0;
				}
			}
		}
		catch(PDOException $e)
		{
			$message = "Critical error in database : insertion failed.";
			print("<h2>$message</h2>");
			apiPHP::write_in_log($e->getMessage()."\n",$this->logPath);
			apiPHP::write_in_log($e->getTraceAsString()."\n",$this->logPath,FALSE);
			die(print($e->getMessage()));
		}
	}

	/**
	 * 
	 * Create save point
	 * @param string $savepointname Save point name
	 */
	function setsavepoint($savepointname)
	{
		try
		{
			$this->dbh->query("SAVEPOINT $savepointname;");
		}
		catch(PDOException $e)
		{
			apiPHP::write_in_log($e->getMessage()."\n",$this->logPath);
			apiPHP::write_in_log($e->getTraceAsString()."\n",$this->logPath,FALSE);
			die(print($e->getMessage()));
		}
	}


	/*
	****************                  ***************
	**************** MODULE REFERENCE ***************
	****************                  ***************
	*/

	/**
	 * 
	 * Select all sources in database
	 * @param string $fonction Function of the sources
	 * @throws Exception
	 */
	function selectAllNameSource($fonction=NULL)
	{
		$arrayNameSource = NULL;
		try
		{
			if(is_null($fonction))
			{
				$query = "SELECT reference_mod.select_all_name_source() AS name_source;";
			}
			else
			{
				$query = "SELECT reference_mod.select_all_name_source('$fonction') AS name_source;";
			}
			$arrayQuery = $this->get($query);
			if(count($arrayQuery)>0)
			{
				$arrayNameSource = $arrayQuery;
			}
			else
			{
				throw new Exception("No source in database with the function $fonction.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayNameSource);
	}
	
	/**
	 * Select source type from its identifier
	 * @param integer $idSource
	 * @throws Exception
	 */
	function selectSourceTypeName($idSource)
	{
		$type = NULL;
		try
		{
			if(is_integer($idSource))
			{
				$query = "SELECT reference_mod.select_source_type_name($idSource) AS type_source;";
				$arrayQuery = $this->get($query);
				if(!is_null($arrayQuery[0]["type_source"]))
				{
					$type = $arrayQuery[0]["type_source"];
				}
				else
				{
					throw new Exception("No source $idSource in database.");
				}
			}
			else
			{
				throw new Exception("Wrong argument idSource $idSource given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($type);
	}
	
	
	/**
	 * Select publication parameters from its db id. TODO manage multiple authors
	 * @param integer $idSource
	 * @throws Exception
	 * @return array
	 */
	function selectPublicationParameters($idSource)
	{
		$arraySource = NULL;
		try
		{
			if(is_integer($idSource))
			{
				$query = "SELECT (reference_mod.select_publication($idSource)).* AS publication_row;";
				$arrayQuery = $this->get($query);
				if(!is_null($arrayQuery[0]["title"]))
				{
					$arraySource = $arrayQuery[0];
					if(count($arrayQuery)>1)
					{
						throw new Exception("Multiple reference parameters with publication identifier $idSource.");
					}
				}
				else
				{
					throw new Exception("No publication $idSource in database.");
				}
			}
			else
			{
				throw new Exception("Wrong argument idSource $idSource given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arraySource);
	}
	
	/**
	 * Select source name from its identifier
	 * @param integer $idSource
	 * @param varcahr $fonction
	 * @throws Exception
	 * @return string
	 */
	function selectNameSource($idSource)
	{
		$nameSource = NULL;
		try
		{
			if(is_integer($idSource))
			{
				$query = "SELECT reference_mod.select_name_source($idSource) AS name_source;";
				$arrayQuery = $this->get($query);
				if(!is_null($arrayQuery[0]["name_source"]))
				{
					$nameSource = $arrayQuery[0]["name_source"];
					if(count($arrayQuery)>1)
					{
						throw new Exception("Multiple sources with source identifier $idSource.");
					}
				}
				else
				{
					throw new Exception("No source $idSource in database.");
				}
			}
			else
			{
				throw new Exception("Wrong argument idSource $idSource given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($nameSource);
	}
	
	/**
	 * 
	 * Insert a source in database
	 * @param string $nameSrc Name of the source
	 * @param string $type Name of type source
	 * @param string $localisation URL or localion
	 * @param string $function Name of the function of the source
	 * @throws Exception
	 */
	function insertSource($nameSrc,$type,$localisation=NULL,$function=NULL)
	{
		//identifiant BD of reference
		$idSrc = NULL;
		try
		{
			if(is_string($nameSrc))
			{
				if(is_string($type))
				{
					$idType = $this->selectTypeSource($type);
					if(!is_null($idType))
					{
						$query = "SELECT reference_mod.source_s_i_u($idType,'$nameSrc'";
						if(is_string($localisation)||is_null($localisation))
						{
							if(!is_null($localisation))
							{
							$query .= ",'$localisation'";
							}
							$query .= ") AS id_src;";
							if(is_string($function)||is_null($function))
							{
								if(!$this->transactionInCourse)
								{
									$this->dbh->beginTransaction();
								}
								if($this->typeInsert=='functionSQL')
								{
									$arrayQuery = $this->get($query);
									if(!is_null($arrayQuery[0]["id_src"]))
									{
										$idSrc = intval($arrayQuery[0]["id_src"]);
										//association to a function
										if(!is_null($function))
										{
											$arrayQuery = $this->get("SELECT reference_mod.insert_source_function_rel(reference_mod.select_fonction_source('$function'),$idSrc) AS my_row;");
											if(is_null($arrayQuery[0]["my_row"]))
											{
												throw new Exception("Association of source $nameSrc to function $function failed.");
											}
										}
									}
									else
									{
										throw new Exception("Insertion of source $nameSrc failed.");
									}
								}
								if(!$this->transactionInCourse)
								{
									$this->dbh->commit();
								}
							}
							else
							{
								throw new Exception("Wrong argument function $function given.");
							}
						}
						else
						{
							throw new Exception("Wrong argument localisation $localisation given.");
						}
					}
					else
					{
						throw new Exception("Problem with source type $type.");
					}					
				}
				else
				{
					throw new Exception("Wrong argument type $type given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument nameSrc $nameSrc given.");
			}
		}
		catch(Exception $e)
		{
			//rollback
			if(!$this->transactionInCourse)
			{
				$this->dbh->rollBack();
			}
			$this->trackException($e);
		}
		return($idSrc);
	}

	/**
	 *
	 * Insertion of a reference and get its identifier, if exists get its identifier. Source should exists a priori
	 * @param $source Source of reference
	 * @param $idInternal Internal identifier in the source
	 * @param $typeSQL TODO Type SQL of identifier
	 */
	function insertSourceReference($source,$idInternal,$typeSQL='string')
	{
		//identifier BD of type
		$idSourceRef = NULL;
		try
		{
			if(is_string($source))
			{
				if(is_string($idInternal))
				{
					//type SQL
					if(intval($idInternal)==$idInternal)
					{
						$typeSQL = 'int';
					}
					//test source existence, update or insertion
					if($this->typeInsert=='functionSQL')
					{
						$arrayQuery = $this->get("SELECT reference_mod.source_ref_s_i('$source','$idInternal') AS id_source_ref");
						if(!is_null($arrayQuery[0]["id_source_ref"]))
						{
							$idSourceRef = intval($arrayQuery[0]["id_source_ref"]);
						}
						else
						{
							throw new Exception("Insertion of $source:$idInternal failed.");
						}
					}
					//DEPRECATED
					else
					{
						$idSourceRef = $this->selectIdRef($source,$idInternal);
						if(is_null($idSourceRef))
						{
							if(!$this->transactionInCourse)
							{
								$this->dbh->beginTransaction();
							}
							$idSourceRef = $this->selectSource($source);
							$arrayParam = array($idSourceRef,$idInternal);
							if($this->exec($this->insertSourcePrepared,$arrayParam,true)!==false)
							{
								$idSourceRef = $this->selectIdRef($source, $idInternal);
							}
							else
							{
								$query = $this->insertSourcePrepared->queryString;
								print("Query $query failed.<br /><br />$query<br />");
							}
							if(!$this->transactionInCourse)
							{
								$this->dbh->commit();
							}
						}
					}
				}
				else
				{
					throw new Exception("Wrong parameter idInternal given.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter source given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idSourceRef);
	}
	
	/**
	 *
	 * Select Db identifier of an global object from its source and its internal identifier in source
	 * @param string $source Source
	 * @param string $idInternal Internal identifier in source. Could be an integer
	 */
	function selectIdObjectRef($idSource,$idInternal)
	{
		$idObject = NULL;
		try
		{
			if(is_integer($idSource))
			{
				if(is_string($idInternal) or is_int($idInternal))
				{
					if($this->typeInsert=='functionSQL')
					{
						$query = "SELECT reference_mod.select_objet_ref($idSource,'$idInternal') AS id_obj";
						$arrayQuery = $this->get($query);
						$idObject = $arrayQuery[0]["id_obj"];
						if(!is_null($idObject))
						{
							$idObject = intval($idObject);
						}
						else
						{
							throw new Exception("No object with reference $idSource:$idInternal in database.");
						}
					}
				}
				else
				{
					throw new Exception("Wrong parameter idInternal $idInternal given.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter idSource $idSource given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idObject);
	}
	
	/**
	 * Select source reference db id from object db id
	 * @param integer $idObject
	 * @throws Exception
	 * @return Ambigous <NULL, integer>
	 */
	function selectObjIdSourceRef($idObject)
	{
		$idSourceRef = NULL;
		try
		{
			if(is_integer($idObject))
			{
				$query = "SELECT reference_mod.select_obj_source_ref_id($idObject) AS id_src_ref;";
				$arrayQuery = $this->get($query);
				if(!is_null($arrayQuery[0]["id_src_ref"]))
				{
					$idSourceRef = intval($arrayQuery[0]["id_src_ref"]);
					//TODO manage multiple source with source function table
					if(count($arrayQuery)>1)
					{
						throw new Exception("Object $idObject has multiple references in database.");
					}
				}
				else
				{
					throw new Exception("Object $idObject has no reference in database.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter idObject $idObject given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idSourceRef);
	}
	
	/**
	 * Select source db id from object db id
	 * @param integer $idObject
	 * @throws Exception
	 * @return Ambigous <NULL, integer>
	 */
	function selectObjIdSource($idObject)
	{
		$idSource = NULL;
		try
		{
			if(is_integer($idObject))
			{
				$query = "SELECT reference_mod.select_obj_source_id($idObject) AS id_src;";
				$arrayQuery = $this->get($query);
				if(!is_null($arrayQuery[0]["id_src"]))
				{
					$idSource = intval($arrayQuery[0]["id_src"]);
					//TODO manage multiple source with source function table
					if(count($arrayQuery)>1)
					{
						throw new Exception("Object $idObject has multiple references in database.");
					}
				}
				else
				{
					throw new Exception("Object $idObject has no reference in database.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter idObject $idObject given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idSource);
	}
	
	/**
	 * Select DB identifier of object's external reference from source and internal identifier in source or insert if not existing
	 * @param $idSource  Source db id source
	 * @param $idInternal Internal identifier in source
	 */
	function selectIdRef($idSource,$idInternal)
	{
		$idRef = NULL;
		try
		{
			if(is_integer($idSource))
			{
				if(is_string($idInternal)||is_null($idInternal))
				{
					$query = "SELECT reference_mod.source_ref_s_i($idSource,";
					if(is_null($idInternal))
					{
						$query .= "NULL";
					}
					else
					{
						$query .= "'$idInternal'";
					}
					$query .= ") AS id_source_ref";
					$arrayQuery = $this->get($query);
					if(!is_null($arrayQuery[0]["id_source_ref"]))
					{
						$idSourceRef = intval($arrayQuery[0]["id_source_ref"]);
					}
					else
					{
						throw new Exception("No source $idSource:$idInternal in database.");
					}
				}
				else
				{
					throw new Exception("Wrong parameter idInternal $idInternal given."); 
				}
			}
			else
			{
				throw new Exception("Wrong parameter idSource $idSource given."); 
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idSourceRef);
	}

	/**
	 * Insertion of link between source and object in database
	 * @param int $idSource
	 * @param int $idObjet
	 * @throws Exception
	 */
    function insertReference($idRef,$idObjet)
    {
		//1 if insertion is ok, else NULL
		$return = NULL;
		try
		{
			if(is_int($idRef))
			{
				if(is_int($idObjet))
				{
					$arrayQuery = $this->get("SELECT reference_mod.insert_objet_ref($idObjet, $idRef);");
					if(!is_null($arrayQuery))
					{
						$return = 1;
					}
					else
					{
						throw new Exception("Insertion of $source:$idInternal failed.");
					}
				}
				else
				{
					throw new Exception("Wrong argument idObjet $idObjet given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument idRef $idRef given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}

	/**
	 * DEPRECATED
	 * Select relationship identifier between object and reference
	 * @param integer $idSource Database identifier of source ref
	 * @param integer $idObject Database identifier of object
	 * @throws Exception
	 */
	function selectIdRefRel($idSource,$idObject)
	{
		$return = NULL;
		try
		{
			if(is_int($idSource))
			{
				if(is_int($idObject))
				{
					$arrayParam = array($idSource,$idObject);
					$arrayQuery = $this->exec($this->queryReferencePrepared, $arrayParam);
					if(count($arrayQuery)>0)
					{
						$return = 1;
					}
					else
					{
						if(count($arrayQuery)==0)
						{
							//							throw new Exception("No source $source with internal identifier $idInternal in database.");
						}
						else
						{
							throw new Exception("More than one references with identifier source $idSource and object $idObject in database.");
						}
					}
				}
				else
				{
					throw new Exception("Wrong parameter idObject $idObject given."); 
				}
			}
			else
			{
				throw new Exception("Wrong parameter idSource $idSource given."); 
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}

	/**
	 *
	 * Insertion of a source author and get its identifier, if exists get directly its identifier 
	 * @param string $author Author name
	 */
	function insertAuthor($author)
	{
		//author identifier
		$idAuthor = NULL;
		try
		{
			//get NULL if not a string not empty
			if(is_string($author) && $author!=='')
			{
				//test author existence, update or insertion
				if($this->typeInsert=='functionSQL')
				{
					$arrayQuery = $this->get("SELECT reference_mod.reference_author_s_i('$author') AS id_author;");
					if(!is_null($arrayQuery[0]["id_author"]))
					{
						$idAuthor = intval($arrayQuery[0]["id_author"]);
					}
					else
					{
						throw new Exception("Insertion of reference author $author failed.");
					}
				}
			}
			else
			{
				throw new Exception("Wrong parameter author $author given.");
			}
		}
		catch(Exception $e)
		{
			if(!$this->transactionInCourse)
			{
				$this->dbh->rollBack();
			}
			$this->trackException($e);
		}
		return($idAuthor);
	}

	/**
	 *
	 * Select DB identifier of author from its name 
	 * @param string $author Author name
	 */
	function selectIdAuthor($author)
	{
		$idAuthor = NULL;
		try
		{
			if(is_string($author))
			{
				$arrayParam = array($author);
				$arrayQuery = $this->exec($this->queryAuthorPrepared, $arrayParam);
				if(!is_null($arrayQuery[0]["id_author"]))
				{
					$idAuthor = intval($arrayQuery[0]["id_author"]);
				}
				else
				{
					throw new Exception("Selecttion of author $author failed.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter author $author given."); 
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idAuthor);
	}

	/**
	 * 
	 * Select a physical type of source 
	 * @param string $type Name of source type
	 * @throws Exception
	 * @return Ambigous <NULL, number>
	 */
	function selectTypeSource($type)
	{
		$idTypeSource = NULL;
		try
		{
			$query = "SELECT reference_mod.select_type_source('$type') AS id_type_source;";
			$arrayQuery = $this->get($query);
			if(!is_null($arrayQuery[0]["id_type_source"]))
			{
				$idTypeSource = intval($arrayQuery[0]["id_type_source"]);
			}
			else
			{
				throw new Exception("No reference type $type in database.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idTypeSource);
	}

	/**
	 *
	 * Insertion of publication
	 * @param string $title Publication title
	 * @param string $edition Edition references
	 * @param string $type Type of publication
	 * @param integer $date Date of publication
	 * @param string $localisation Location
	 * @param string $function Function
	 * @throws Exception
	 * @return integer,NULL
	 */
	function insertPub($title,$edition,$type,$date=NULL,$localisation=NULL,$function=NULL)
	{
		//DB identifier of reference
		$idPub = NULL;
		try
		{
			if(is_string($title))
			{
				if(is_string($edition))
				{
					if(is_string($type))
					{
						$idType = $this->selectTypeSource($type);
						if(!is_null($idType))
						{
							$query = "SELECT reference_mod.publication_s_i_u($idType,'$title','$edition'";
							if(is_string($date)||is_null($date))
							{
								if(!is_null($date))
								{
									$query .= ",$date";
								}
								if(is_string($localisation)||is_null($localisation))
								{
									if(!is_null($localisation))
									{
										$query .= ",'$localisation'";
									}
									$query .= ") AS id_pub;";
									if(is_string($function)||is_null($function))
									{
										if(!$this->transactionInCourse)
										{
											$this->dbh->beginTransaction();
										}
										if($this->typeInsert=='functionSQL')
										{
											$arrayQuery = $this->get($query);
											if(!is_null($arrayQuery[0]["id_pub"]))
											{
												$idPub = intval($arrayQuery[0]["id_pub"]);
												//association to a fonction
												if(!is_null($function))
												{
													$arrayQuery = $this->get("SELECT reference_mod.insert_source_function_rel(reference_mod.select_fonction_source('$function'),$idPub) AS my_row;");
													if(is_null($arrayQuery[0]["my_row"]))
													{
														throw new Exception("Association with function $function failed for publication $title.");
													}
												}
											}
											else
											{
												throw new Exception("Insertion of publication $title of $nameAuthor failed.");
											}
										}
										if(!$this->transactionInCourse)
										{
											$this->dbh->commit();
										}
									}
									else
									{
										throw new Exception("Wrong argument function $function given.");
									}
								}
								else
								{
									throw new Exception("Wrong argument localisation $localisation given.");
								}
							}
							else
							{
								throw new Exception("Wrong argument date $date given.");
							}
						}
						else
						{
							throw new Exception("Problem with source type $type.");
						}
					}
					else
					{
						throw new Exception("Wrong argument type $type given.");
					}
				}
				else
				{
					throw new Exception("Wrong argument edition $edition given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument title $title given.");
			}
		}
		catch(Exception $e)
		{
			if(!$this->transactionInCourse)
			{
				$this->dbh->rollBack();
			}
			$this->trackException($e);
		}
		return($idPub);
	}

	/**
	 * DEPRECATED
	 * Select a publication from its name
	 * @param string $pub Publication name
	 * @throws Exception
	 * @return Ambigous <NULL, number>
	 */
	function selectIdPub($pub)
	{
		$idPub = NULL;
		try
		{
			if(is_string($pub))
			{
				$arrayParam = array($pub);
				$arrayQuery = $this->exec($this->queryPubPrepared, $arrayParam);
				if(count($arrayQuery)==1)
				{
					$idPub = intval($arrayQuery[0]["id_pub"]);
				}
				else
				{
					if(count($arrayQuery)==0)
					{
						// 							throw new Exception("No pub $pub in database.");
					}
					else
					{
						throw new Exception("More than one publications $pub in database.");
					}
				}
			}
			else
			{
				throw new Exception("Wrong parameter pub $pub given."); 
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idPub);
	}


	/**
	 * DEPRECATED
	 * Insertion of relation object/object for publications
	 * @param int $idPub Identifier of father publication
	 * @param int $idPubIncluded Identifier of included publication
	 * @throws Exception
	 * @return Ambigous <NULL, number>
	 */
	function insertPubRelationship($idPub,$idPubIncluded)
	{
		$return = NULL;
		try
		{
			if(is_int($idPub))
			{
				if(is_int($idPubIncluded))
				{
					//test existence of relationship
					$arrayParam = array($idPub,$idPubIncluded);
					if(count($arrayQuery=$this->exec($this->queryPubRelPrepared, $arrayParam))==0)
					{
						if(!$this->transactionInCourse)
						{
							$this->dbh->beginTransaction();
						}
						if($this->exec($this->insertPubRelPrepared,$arrayParam,true)!==false)
						{
							$return = 1;
						}
						else
						{
							$query = $this->insertPubRelPrepared->queryString;
							print("Query $query failed.<br /><br />$query<br />");
						}
						if(!$this->transactionInCourse)
						{
							$this->dbh->commit();
						}
					}
				}
				else
				{
					throw new Exception("Wrong argument idPubIncluded given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument idPub given.");
			}
		}
		catch(Exception $e)
		{
			if(!$this->transactionInCourse)
			{
				$this->dbh->rollBack();
			}
			$this->trackException($e);
		}
		return($return);
	}

	/**
	 *
	 * Insertion of relationship between author and publication with author insertion if not existing
	 * @param integer $idPub Database identifier of publication
	 * @param string $nameAuthor Author name
	 * @throws Exception
	 * @return array
	 */
	function insertPubAuthor($idPub,$nameAuthor)
	{
		$return = NULL;
		try
		{
			if(is_int($idPub))
			{
				if(is_string($nameAuthor))
				{
					if(!$this->transactionInCourse)
					{
						$this->dbh->beginTransaction();
					}
					if($this->typeInsert=='functionSQL')
					{
						$arrayQuery = $this->get("SELECT publish_s_i[1] AS id_aut,publish_s_i[2] AS id_pub FROM ".
							"(SELECT reference_mod.publish_s_i(reference_mod.reference_author_s_i('$nameAuthor'),$idPub)) AS my_array;");
						if(!is_null($arrayQuery[0]["id_pub"]))
						{
							$idAuthor = intval($arrayQuery[0]["id_aut"]);
							$idPub = intval($arrayQuery[0]["id_pub"]);
							$return = array($idAuthor,$idPub);
						}
						else
						{
							throw new Exception("Insertion of relationship between author $nameAuthor and reference $idPub failed.");
						}
					}
					if(!$this->transactionInCourse)
					{
						$this->dbh->commit();
					}
				}
				else
				{
					throw new Exception("Wrong argument nameAuthor $nameAuthor given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument idPub $idPub given.");
			}
		}
		catch(Exception $e)
		{
			if(!$this->transactionInCourse)
			{
				$this->dbh->rollBack();
			}
			$this->trackException($e);
		}
		return($return);
	}
		

	/*
	****************             ***************
	**************** MODULE TAXO ***************
	****************             ***************
	*/

	
	/**
	 * 
	 * Select taxon database identifier given its complete name
	 * @param string $nameSpecies Complete name of taxon
	 * @throws Exception
	 * @return Ambigous <NULL, number>
	 */
	function NewSelectIdRealTaxon($nameSpecies,$real=TRUE)
	{
		$idTaxon = NULL;
		$queryPrepared = NULL;
		$arrayParam = array();
		try
		{
			if(is_string($nameSpecies))
			{
				if($real)
				{
					$query = "SELECT taxonomy_mod.select_taxo_complete('$nameSpecies') AS id_taxon;";
				}
				else
				{
					$query = "SELECT taxonomy_mod.select_taxo_complete_brut('$nameSpecies') AS id_taxon;";
				}
				$arrayQuery = $this->get($query);
				$idTaxon = $arrayQuery[0]["id_taxon"];
				if(!is_null($idTaxon))
				{
					$idTaxon = intval($idTaxon);
				}
				else
				{

					print("The taxon '$nameSpecies' didn't exist in the database");

					if(!preg_match("/^[\w\(\)\,\. ]+$/", $taxonName))
					{
						throw new Exception("Wrong taxon name $taxonName given");
					}
					else
					{
						//file name is taxon name
						$output .= "$taxonName.csv";
						if(!file_exists($output)||$cached!='1')
						{
							$eolTaxonId = NULL;
							$exactMatch = 'true';
							$jsonEolSearch = "http://eol.org/api/search/1.0.json?q=$taxonName&page=1&exact=$exactMatch&filter_by_taxon_concept_id=&filter_by_hierarchy_entry_id=$eolTaxonId&filter_by_string=&cache_ttl=".constant("CACHE")."&key=".constant("EOL_KEY");
							$eolSearch = eolApiCall($jsonEolSearch);
							if($eolSearch->totalResults=='0')
							{
								throw new Exception("No result");
							}
							else
							{
								if($eolSearch->totalResults!='1')
								{
									print("More than one result for taxon $taxonName");
								}
							}
							//get only the id of first result
							$eolPageId = $eolSearch->results[0]->id;
							$trustedResult = '0';
							//print("Page ok<br />");
							
							//get the page informations to extract taxon concept id
							$jsonEolPage = "http://eol.org/api/pages/1.0/$eolPageId.json?images=0&videos=0&sounds=0&maps=0&text=0&iucn=false&subjects=overview&licenses=all&details=false&common_names=false&synonyms=false&references=false&vetted=$trustedResult&cache_ttl=".constant("CACHE")."&key=".constant("EOL_KEY");
							$eolPage = eolApiCall($jsonEolPage);
							$find = FALSE;
							//find preferred source
							foreach($eolPage->taxonConcepts as $taxonConcept)
							{
								if(!$find)
								{
									if($taxonConcept->nameAccordingTo==constant("SOURCE_1")||$taxonConcept->nameAccordingTo==constant("SOURCE_2"))
									{
										$eolTaxonId = $taxonConcept->identifier;
										$find = TRUE;
										break;
									}
								}
							}
							//take first occurence if preferred source is not found
							if(!$find)
							{
								$eolTaxonId = $eolPage->taxonConcepts[0]->identifier;
							}
							//print("Taxon concept ok<br />");
							
							if(!$fp=fopen($output, "w"))
							{
								throw new Exception("Cannot write in file $output");
							}
							fwrite($fp,"name\trank\t\ttaxon_id\ttaxon_father_id\ttaxon_real_id\n");
							
							//get hierarchy
							$jsonEolHierarchy = "http://eol.org/api/hierarchy_entries/1.0/$eolTaxonId.json?common_names=false&synonyms=false&cache_ttl=".constant("CACHE")."&key=".constant("EOL_KEY");
							$eolHierarchy = eolApiCall($jsonEolHierarchy);
							//set current taxon
							$arrayEolTaxon[$eolHierarchy->taxonID]["parent"] = $eolHierarchy->parentNameUsageID;
							$arrayEolTaxon[$eolHierarchy->taxonID]["id"] = $eolHierarchy->taxonID;
							$arrayEolTaxon[$eolHierarchy->taxonID]["name"] = $eolHierarchy->scientificName;
							$arrayEolTaxon[$eolHierarchy->taxonID]["rank"] = $eolHierarchy->taxonRank;
							fwrite($fp,$arrayEolTaxon[$eolHierarchy->taxonID]["name"]."\t".$arrayEolTaxon[$eolHierarchy->taxonID]["rank"]."\t"."\t".$arrayEolTaxon[$eolHierarchy->taxonID]["id"]."\t".$arrayEolTaxon[$eolHierarchy->taxonID]["parent"]."\t".$arrayEolTaxon[$eolHierarchy->taxonID]["id"]."\n");
							//get parents
							foreach($eolHierarchy->ancestors as $parent)
							{
								if(!key_exists($parent->taxonID, $arrayEolTaxon))
								{
									$arrayEolTaxon[$parent->taxonID]["parent"] = $parent->parentNameUsageID;
									$arrayEolTaxon[$parent->taxonID]["id"] = $parent->taxonID;
									$arrayEolTaxon[$parent->taxonID]["name"] = $parent->scientificName;
									$arrayEolTaxon[$parent->taxonID]["rank"] = $parent->taxonRank;
									fwrite($fp,$arrayEolTaxon[$parent->taxonID]["name"]."\t".$arrayEolTaxon[$parent->taxonID]["rank"]."\t"."\t".$arrayEolTaxon[$parent->taxonID]["id"]."\t".$arrayEolTaxon[$parent->taxonID]["parent"]."\t".$arrayEolTaxon[$parent->taxonID]["id"]."\n");
								}
							}
							
							//get children and to recursion on it
							/*foreach($eolHierarchy->children as $child)
							{
								if(!key_exists($child->taxonID, $arrayEolTaxon))
								{
									$arrayEolTaxon[$child->taxonID]["parent"] = $child->parentNameUsageID;
									$arrayEolTaxon[$child->taxonID]["id"] = $child->taxonID;
									$arrayEolTaxon[$child->taxonID]["name"] = $child->scientificName;
									$arrayEolTaxon[$child->taxonID]["rank"] = $child->taxonRank;
									$arrayEolTaxon = eolHierarchyRecursive($child->taxonID,$arrayEolTaxon,$fp);
								}
							}*/

							//print("Hierarchy ok<br />");
							fclose($fp);
						}
					}
				}
			}
			else
			{
				throw new Exception("Wrong parameter nameSpecies $nameSpecies given."); 
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idTaxon);
	}


	/**
	 * 
	 * Select taxon database identifier given its complete name
	 * @param string $nameSpecies Complete name of taxon
	 * @throws Exception
	 * @return Ambigous <NULL, number>
	 */
	function selectIdRealTaxon($nameSpecies,$real=TRUE)
	{
		$idTaxon = NULL;
		$queryPrepared = NULL;
		$arrayParam = array();
		try
		{
			if(is_string($nameSpecies))
			{
				if($real)
				{
					$query = "SELECT taxonomy_mod.select_taxo_complete('$nameSpecies') AS id_taxon;";
				}
				else
				{
					$query = "SELECT taxonomy_mod.select_taxo_complete_brut('$nameSpecies') AS id_taxon;";
				}
				$arrayQuery = $this->get($query);
				$idTaxon = $arrayQuery[0]["id_taxon"];
				if(!is_null($idTaxon))
				{
					$idTaxon = intval($idTaxon);
				}
				else
				{
					throw new Exception("Error: The taxon '$nameSpecies' doesn't exist in the database");
				}
			}
			else
			{
				throw new Exception("Wrong parameter nameSpecies $nameSpecies given."); 
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idTaxon);
	}

	/**
	 *
	 * Select taxon complete name given its database identifier
	 * @param integer $idTaxon Database identifier of taxon
	 * @throws Exception
	 * @return Ambigous <NULL, string>
	 */
	function selectNameRealTaxon($idTaxon)
	{
		$nameTaxon = NULL;
		try
		{
			if(is_integer($idTaxon))
			{
				$query = "SELECT taxonomy_mod.select_taxo_name($idTaxon) AS name_taxon;";
				$arrayQuery = $this->get($query);
				$nameTaxon = $arrayQuery[0]["name_taxon"];
				if(is_null($nameTaxon))
				{
					throw new Exception("Select taxon name with identifier $idTaxon failed.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter idTaxon $idTaxon given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($nameTaxon);
	}
	
	/**
	 * 
	 * Select taxon father database identifier
	 * @param integer $idTaxon DB identifier of taxon
	 * @throws Exception
	 * @return integer
	 */
	function selectIdTaxonFather($idTaxon)
	{
		$idTaxonFather = NULL;
		try
		{
			if(is_int($idTaxon))
			{
				if($this->typeInsert=='functionSQL')
				{
					$query = "SELECT taxonomy_mod.taxo_father_s($idTaxon) AS id_father;";
					$arrayQuery = $this->get($query);
					$idTaxonFather = $arrayQuery[0]["id_father"];
					if(!is_null($idTaxonFather))
					{
						$idTaxonFather = intval($idTaxonFather);
					}
					else
					{
						throw new Exception("Taxon $idTaxon has no father");
					}
				}
			}
			else
			{
				throw new Exception("Wrong parameter idTaxon $idTaxon given."); 
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idTaxonFather);
	}

	/**
	 *
	 * Select Db identifier of a taxon from its source and its internal identifier in source
	 * @param string $source Taxon source
	 * @param string $idInternal Internal identifier in source. Could be an integer
	 */
	function selectIdTaxonFromRef($source,$idInternal)
	{
		$idTaxon = NULL;
		try
		{
			if(is_string($source))
			{
				if(is_string($idInternal) or is_int($idInternal))
				{
					if($this->typeInsert=='functionSQL')
					{
						$arrayQuery = $this->get("SELECT reference_mod.select_objet_ref(reference_mod.select_source('$source'),'$idInternal')".
							"AS id_taxon");
						$idTaxon = $arrayQuery[0]["id_taxon"];
						if(!is_null($idTaxon))
						{
							$idTaxon = intval($idTaxon);
						}
						else
						{
							//case of artefact taxon or taxon with double or with +
							throw new Exception("No taxon with refernce $source:$idInternal in database.");
						}
					}
				}
				else
				{
					throw new Exception("Wrong parameter idSource $idInternal given."); 
				}
			}
			else
			{
				throw new Exception("Wrong parameter source $source given."); 
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idTaxon);
	}

	/**
	 * DEPRECATED
	 * Select DB identifier of a taxon author from its name
	 * @param $author Author name
	 */
	function selectIdTaxoAuthor($author)
	{
		$idAuthor = NULL;
		try
		{
			if(is_string($author))
			{
				$arrayParam = array($author);
				$arrayQuery = $this->exec($this->queryAuthorTaxoPrepared, $arrayParam);
				if(count($arrayQuery)==1)
				{
					$idAuthor = intval($arrayQuery[0]["id_author"]);
				}
				else
				{
					if(count($arrayQuery)==0)
					{
						// 						throw new Exception("Pas d'auteur $author in database.");
					}
					else
					{
						throw new Exception("More than one auteurs $author in database.");
					}
				}
			}
			else
			{
				throw new Exception("Wrong parameter auteur $author given."); 
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idAuthor);
	}

	/**
	 *
	 * Insertion of a taxon author returning its identifier, if exists return its identifier only
	 * @param string $author Taxon author name
	 */
	function insertAuthorTaxo($author)
	{
		//author identifier
		$idAuthor = NULL;
		try
		{
			//return NULL if not character string not empty
			if(is_string($author) && $author!=='')
			{
				//test existence of taxon author, update or insertion
				if($this->typeInsert=='functionSQL')
				{
					$arrayQuery = $this->get("SELECT taxonomy_mod.auteur_taxo_s_i('$author') AS id_author;");
					if(!is_null($arrayQuery[0]["id_author"]))
					{
						$idAuthor = intval($arrayQuery[0]["id_author"]);
					}
					else
					{
						throw new Exception("Insertion of taxon author $author failed.");
					}
				}
				//DEPRECATED
				else
				{
					$idAuthor = $this->selectIdAuthor($author);
					if(!(is_int($idAuthor) && $idAuthor!==false))
					{
						if(!$this->transactionInCourse)
						{
							$this->dbh->beginTransaction();
						}
						$arrayParam = array($author);
						if($this->exec($this->insertAuthorPrepared,$arrayParam,true)!==false)
						{
							$idAuthor = $this->selectIdAuthor($author);
						}
						else
						{
							$query = $this->insertAuthorPrepared->queryString;
							print("Query $query failed.<br /><br />$query<br />");
						}
						if(!$this->transactionInCourse)
						{
							$this->dbh->commit();
						}
					}
				}
			}
			else
			{
				throw new Exception("Wrong parameter auteur $author given.");
			}
		}
		catch(Exception $e)
		{
			if(!$this->transactionInCourse)
			{
				$this->dbh->rollBack();
			}
			$this->trackException($e);
		}
		return($idAuthor);
	}


	/**
	 *
	 * Select DB identifier of taxonomic type from its name
	 * @param string $type Taxon type
	 */
	function selectIdTypeTaxon($type)
	{
		$idType = NULL;
		try
		{
			if(is_string($type))
			{
				$arrayParam = array($type);
				if($this->typeInsert=='functionSQL')
				{
					$arrayQuery = $this->get("SELECT taxonomy_mod.select_type_taxonomy('$type') AS id_type");
				}
				else
				{
					$arrayQuery = $this->exec($this->queryTypePrepared, $arrayParam);
				}
				if(!is_null($arrayQuery[0]["id_type"]))
				{
					$idType = intval($arrayQuery[0]["id_type"]);
				}
				else
				{
					throw new Exception("Selection of taxonoomy type $type failed.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter type $type given."); 
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idType);
	}

	/**
	 *
	 * Select DB identifier of an taxonomic entry from its complete name and its type
	 * @param string $nameOriginal Original name of taxon
	 * @param integer $idType DB identifier of taxon type
	 */
	function selectIdTaxon($nameComplete,$idType)
	{
		$idTaxon = NULL;
		try
		{
			if(is_string($nameComplete))
			{
				if(is_int($idType))
				{
					$arrayQuery = $this->get("SELECT taxonomy_mod.taxo_s('$nameComplete',$idType) AS id_taxon");
					$idTaxon = $arrayQuery[0]["id_taxon"];
					if(is_null($idTaxon))
					{
						throw new Exception("No taxon $nameComplete avec le type $idType in database.");
					}
					else
					{
						$idTaxon = intval($idTaxon);
					}
				}
				else
				{
					throw new Exception("Wrong parameter idType $idType given."); 
				}
			}
			else
			{
				throw new Exception("Wrong parameter nameOriginal $nameComplete given."); 
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idTaxon);
	}


	/**
	 *DEPRECATED
	 * Insertion of one taxonomic type and get its identifier back, get only its identifier if existing
	 * @param string $type taxon type
	 */
	function insertTaxoType($type)
	{
		//identifiant BD du type
		$idType = NULL;
		try
		{
			if(is_string($type))
			{
				if(!$this->transactionInCourse)
				{
					$this->dbh->beginTransaction();
				}
				if(!$this->transactionInCourse)
				{
					$this->dbh->commit();
				}
			}
			else
			{
				throw new Exception("Wrong parameter type taxo $type given.");
			}
		}
		catch(Exception $e)
		{
			if(!$this->transactionInCourse)
			{
				$this->dbh->rollback();
			}
			$this->trackException($e);
		}
		return($idType);
	}

	/**
	 * 
	 * Insertion af a taxon from the object class
	 * @param object $myTaxonObject
	 * @throws Exception
	 * @return Ambigous <NULL, integer>
	 */
	function insertTaxo($myTaxonObject)
	{
		//taxon identifier
		$idTaxon = NULL;
		$idType = NULL;
		$idSourceRef = NULL;
		$idAuthor = NULL;
		$nameTaxon = NULL;
		$nameCompleteTaxon = NULL;
		$source = NULL;
		$sourceRef = NULL;
		$rank = NULL;
		try
		{
			if(is_a($myTaxonObject, 'Taxo'))
			{
				if(!$this->transactionInCourse)
				{
					$this->dbh->beginTransaction();
				}
				//assignation of object variables
				$nameCompleteTaxon = $myTaxonObject->get("nom_complet");
				$author = $myTaxonObject->get("auteur");
				$source = $myTaxonObject->get("source");
				$sourceRef = $myTaxonObject->get("id_interne");
				$rank = $myTaxonObject->get("rang");
				//gets rank id
				$idType = $this->selectIdTypeTaxon($rank);
				if(!is_int($idType))
				{
					throw new Exception('Problème de rang in database');
				}
				//insert metadata

				//select taxon author and insertion if any
				if(is_null($author)||strlen(trim($author))==0)
				{
					$idAuthor = NULL;
				}
				else
				{
					$idAuthor = $this->insertAuthorTaxo($author);
				}
				//teste taxon existence (complete name/rank), update or insertion
				if($this->typeInsert=='functionSQL')
				{
					if(is_null($idAuthor))
					{
						$arrayQuery = $this->get("SELECT taxonomy_mod.taxo_s_i_u('$nameCompleteTaxon',$idType) AS id_taxon");
					}
					else
					{
						$arrayQuery = $this->get("SELECT taxonomy_mod.taxo_s_i_u('$nameCompleteTaxon',$idType,$idAuthor) AS id_taxon");
					}
					if(!is_null($arrayQuery[0]["id_taxon"]))
					{
						$idTaxon = intval($arrayQuery[0]["id_taxon"]);
						if(!is_null($sourceRef))
						{
							if(preg_match("/^([a-zA-Z0-9]+)_[0-9]+$/",$sourceRef,$arrayMatch))
							{
								$source = $arrayMatch[1];
								$this->insertSource($source,'Unpublished work','BETSI','taxonomy');
							}
							//insertion des references internes (source + référence)
							$idSourceRef = $this->insertSourceReference($source,$sourceRef);
							//insertion de lien avec les references
							$arrayQuery = $this->get("SELECT reference_mod.insert_objet_ref($idTaxon,$idSourceRef);");
							if(is_null($arrayQuery[0]["insert_objet_ref"]))
							{
								throw new Exception("Insertion du taxon échoué.");
							}
						}
					}
					else
					{
						throw new Exception("Insertion of taxon $nameCompleteTaxon with rank $rank failed in database.");
					}
				}
				else
				{
					//others insertions types
				}
				if(!$this->transactionInCourse)
				{
					$this->dbh->commit();
				}
			}
			else
			{
				throw new Exception("Wrong parameter object taxo given.");
			}
		}
		catch(Exception $e)
		{
			if(!$this->transactionInCourse)
			{
				$this->dbh->rollBack();
			}
			$this->trackException($e);
		}
		return($idTaxon);
	}

	/**
	 *
	 * Insert relationship between taxon
	 * @param string $source Taxons' source
	 * @param integer $idOriginal DB identifier of true taxon
	 * @param integer $idParent DB identifier taxon's father
	 * @param integer $idSyn DB identifier of true taxon synonym
	 */
	function insertRelation($source,$idRefOriginal,$idRefParent,$idRefSyn,$name,$rank)
	{
		//1 if insertion is ok, else NULL
		$return = NULL;
		try
		{
			if(is_string($name))
			{
				if(is_string($rank))
				{
					if(is_null($idRefOriginal))
					{
						$idOriginal = $this->selectIdTaxon($name, $this->selectIdTypeTaxon($rank));
					}
					else
					{
						//patch for add new source in complement of Fauna Europaea
						if(preg_match("/^([a-zA-Z0-9]+)_[0-9]+$/",$idRefOriginal,$arrayMatch))
						{
							$sourceAlt = $arrayMatch[1];
						}
						else
						{
							$sourceAlt = $source;
						}
						$idOriginal = $this->selectIdTaxonFromRef($sourceAlt,$idRefOriginal);
					}
					var_dump($idRefParent);
					if(is_null($idRefParent)||$idRefParent===0)
					{
						//patch for add new source in complement of Fauna Europaea
						if(preg_match("/^([a-zA-Z0-9]+)_[0-9]+$/",$idRefSyn,$arrayMatch))
						{
							$sourceAlt = $arrayMatch[1];
						}
						else
						{
							$sourceAlt = $source;
						}
						$idParent = $this->selectIdTaxonFather($this->selectIdTaxonFromRef($sourceAlt,$idRefSyn));
					}
					else
					{
						//patch for add new source in complement of Fauna Europaea
						if(preg_match("/^([a-zA-Z0-9]+)_[0-9]+$/",$idRefParent,$arrayMatch))
						{
							$sourceAlt = $arrayMatch[1];
						}
						else
						{
							$sourceAlt = $source;
						}
						$idParent = $this->selectIdTaxonFromRef($sourceAlt,$idRefParent);
					}
					//patch for add new source in complement of Fauna Europaea
					if(preg_match("/^([a-zA-Z0-9]+)_[0-9]+$/",$idRefSyn,$arrayMatch))
					{
						$sourceAlt = $arrayMatch[1];
					}
					else
					{
						$sourceAlt = $source;
					}
					$idSyn = $this->selectIdTaxonFromRef($sourceAlt,$idRefSyn);
					//TODO patch for FaunaEuropaea wrong taxon
					if(is_null($idSyn))
					{
						$idSyn = $idOriginal;
						print("To patch $idRefOriginal,$name,$rank<br />");
					}
					if(is_int($idOriginal))
					{
						if(is_int($idParent))
						{
							if(is_int($idSyn))
							{
								if(!$this->transactionInCourse)
								{
									$this->dbh->beginTransaction();
								}
								if($this->typeInsert=='functionSQL')
								{
									$arrayQuery = $this->get("SELECT taxonomy_mod.relation_u($idOriginal,$idParent,$idSyn);");
									if(count($arrayQuery)==0)
									{
										throw new Exception("Update failed.");
									}
								}
								else
								{
								}
								if(!$this->transactionInCourse)
								{
									$this->dbh->commit();
								}
							}
							else
							{
								throw new Exception("Wrong argument idSyn.");
							}
						}
						else
						{
							throw new Exception("Wrong argument idParent.");
						}
					}
					else
					{
						throw new Exception("Wrong argument idOriginal.");
					}
				}
				else
				{
					throw new Exception("Wrong argument rank given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument name given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}


	/**
	 * DEPRECATED
	 * Teste synonyms existence
	 * @param integer $idOriginal
	 * @param integer $idSyn
	 * @throws Exception
	 * @return Ambigous <NULL, number>
	 */
	function selectSynonym($idOriginal,$idSyn)
	{
		$result = NULL;
		try
		{
			if(is_int($idOriginal))
			{
				if(is_int($idSyn))
				{
					$arrayParam = array($idOriginal,$idSyn);
					$arrayQuery = $this->exec($this->querySynonymPrepared, $arrayParam);
					$result = count($arrayQuery);
					if($result==0)
					{
						$result = NULL;
					}
					else
					{
						if($result>1)
						{
							throw new Exception("More than one synonyms $idOriginal,$idSyn in database.");
						}
					}
				}
				else
				{
					throw new Exception("Wrong argument idSyn given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument idOriginal given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($result);
	}

	/**
	 *
	 * Insertion of synonymy relationship between taxons
	 * @param integer $idOriginal DB identifier of true taxon
	 * @param integer $idSyn DB identifier of synonym taxon
	 */
	function insertSyn($idOriginal,$idSyn)
	{
		//1 if insertion is ok, else NULL
		$return = NULL;
		try
		{
			if(is_int($idOriginal))
			{
				if(is_int($idSyn))
				{
					if($this->typeInsert=='functionSQL')
					{
						$arrayQuery = $this->get("PERFORM reference_mod.synonym_s_i($idOriginal,$idSyn);");
					}
					else
					{
						//DEPRECATED test synonymy relationship existence
						if(is_null($this->selectSynonym($idOriginal,$idSyn)))
						{
							$this->dbh->beginTransaction();
							$arrayParam = array($idOriginal,$idSyn);
							if($this->exec($this->insertSynonymPrepared,$arrayParam,true)!==false)
							{
								$return = 1;
							}
							else
							{
								$query = $this->insertSynonymPrepared->queryString;
								throw new Exception("Query $query failed.<br /><br />$query<br />");
							}
							$this->dbh->commit();
						}
					}
				}
				else
				{
					throw new Exception("Wrong argument idSyn given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument idOriginal given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}

	/*
	****************              ***************
	**************** MODULE RIGHT ***************
	****************              ***************
	*/

	 /**
	 * Select all trait source id + owner
	 * @throws Exception
	 */
	function selectAllSourceByOwner()
	{
		$arrayCouple = NULL;
		try
		{
			$query = "SELECT iraw.\"owner\" AS \"owner\", icoded.\"owner\" AS coder, reference_mod.select_obj_source_id(trait_value_id) AS source_id, reference_mod.select_obj_source_id(trait_value_father_id) AS source_father_id, ".
					"my_string_agg(iraw.embargo_end) AS raw_release, my_string_agg(icoded.embargo_end) AS coded_release, CAST(iraw.accessibility AS INTEGER) AS raw_accessibility, CAST(icoded.accessibility AS INTEGER) AS coded_accessibility ".
				"FROM (trait_mod.trait_value ".
					"LEFT JOIN copyright_mod.copyright ON(trait_value_id=global_identifier_id) ".
					"LEFT JOIN copyright_mod.copyright_info USING (global_identifier_id)) AS iraw ".
					"LEFT JOIN (trait_mod.trait_coded_value ".
					"LEFT JOIN copyright_mod.copyright ON(trait_coded_value_id=global_identifier_id) ".
					"LEFT JOIN copyright_mod.copyright_info USING(global_identifier_id)) AS icoded USING(trait_value_id) ".
				"WHERE sample_id IS NULL AND (icoded.\"owner\"=iraw.\"owner\" OR icoded.\"owner\" IS NULL) ".
				"GROUP BY iraw.\"owner\", coder, source_id, source_father_id, raw_accessibility, coded_accessibility ".
				"ORDER BY source_id, \"owner\";";
			$arrayQuery = $this->get($query);
			if(!is_null($arrayQuery[0]["source_id"]))
			{
				$arrayCouple = $arrayQuery;
			}
			else
			{
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayCouple);
	}

	/**
	 * Select all copyright for sampling + infos
	 * @throws Exception
	 */
	function selectAllSamplingCopyright()
	{
		$arraySampling = NULL;
		try
		{
			$query = "SELECT sampling_id,metadata_mod.select_metadata_value(metadata_mod.select_metadata_type('collector'),sampling_id) AS collector, ".
					"metadata_mod.select_metadata_value(metadata_mod.select_metadata_type('creation date'),sampling_id) AS creation_date, ".
					"metadata_mod.select_metadata_value(metadata_mod.select_metadata_type('in charge collector'),sampling_id) AS in_charge_collector, ".
					"metadata_mod.select_metadata_value(metadata_mod.select_metadata_type('identifier'),sampling_id) AS identifier, ". 
					"embargo_end AS release_date, ".
					"copyright_mod.select_copyright(sampling_id) AS copyright, ".
					"experience_mod.select_sampling_type(sampling_id) AS name_type, ".
					"(reference_mod.select_publication(reference_mod.select_obj_source_id(sampling_id))).* ".
				"FROM experience_mod.sampling ".
					"LEFT JOIN copyright_mod.copyright ON(sampling_id=global_identifier_id) ".
					"LEFT JOIN copyright_mod.copyright_info USING(global_identifier_id);";
			$arrayQuery = $this->get($query);
			if(!is_null($arrayQuery[0]["sampling_id"]))
			{
				$arraySampling = $arrayQuery;
			}
			else
			{
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arraySampling);
	}


	/**
	 * Select copyright value
	 * @param integer $idObj Global identifier of a database object
	 * @throws Exception
	 */
	function selectCopyright($idObj)
	{
		$copyright = NULL;
		try
		{
			if(is_integer($idObj))
			{
				$query = "SELECT copyright_mod.select_copyright($idObj) AS copyright;";
				$arrayQuery = $this->get($query);
				if(!is_null($arrayQuery[0]["copyright"]))
				{
					$copyright = intval($arrayQuery[0]["copyright"]);
				}
				else
				{
				}
			}
			else
			{
				throw new Exception("Wrong parameter idObj $idObj given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($copyright);
	}

	/**
	 * Select copyright release date
	 * @param integer $idObj Global identifier of a database object
	 * @throws Exception
	 */
	function selectCopyrightReleaseDate($idObj)
	{
		$date = NULL;
		try
		{
			if(is_integer($idObj))
			{
				$query = "SELECT copyright_mod.select_copyright_embargo_end($idObj) AS date;";
				$arrayQuery = $this->get($query);
				if(!is_null($arrayQuery[0]["date"]))
				{
					$date = $arrayQuery[0]["date"];
				}
				else
				{
				}
			}
			else
			{
				throw new Exception("Wrong parameter idObj $idObj given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
	}

	function insertCopyright($idObj,$accessibility,$owner,$creationDate)
	{
		$return = NULL;
		try
		{
			if(is_int($idObj))
			{
				if(apiPHP::my_is_int($accessibility))
				{
					if(is_string($owner)||apiPHP::my_is_null($owner))
					{
						if(is_string($creationDate)||apiPHP::my_is_null($creationDate))
						{
							if(!$this->transactionInCourse)
							{
								$this->dbh->beginTransaction();
							}
							$query = "SELECT copyright_mod.copyright_i($idObj";
							if($accessibility=='0')
							{
								$query .= ",TRUE";
							}
							else
							{
								$query .= ",FALSE";
							}
							if(!apiPHP::my_is_null($owner))
							{
								$query .= ",'$owner'";
							}
							if(!apiPHP::my_is_null($creationDate))
							{
								//add embargo duration to creation date
								$arrayCreationDate = explode("/",$creationDate);
								$day = $arrayCreationDate[0];
								$month = $arrayCreationDate[1];
								$year = intval($arrayCreationDate[2]) + intval($accessibility);
								$embargoEnd = "$day/$month/$year";
								$query .= ",'$embargoEnd'";
							}
							$query .= ") AS id_obj;";
							$arrayQuery = $this->get($query);
							if(!is_null($arrayQuery[0]["id_obj"]))
							{
								$return = intval($arrayQuery[0]["id_obj"]);
							}
							else
							{
								throw new Exception("Insertion of copyright for object $idObj failed.");
							}
							if(!$this->transactionInCourse)
							{
								$this->dbh->commit();
					       		}
						}
						else
						{
							throw new Exception("Wrong parameter creation $creationDate given.");
						}
					}
					else
					{
						throw new Exception("Wrong parameter owner $owner given.");
					}
				}
				else
				{
					throw new Exception("Wrong parameter accessibility $accessibility given.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter idObj $idObj given.");
			}
		}
		catch(Exception $e)
		{
			if(!$this->transactionInCourse)
			{
				$this->dbh->rollBack();
			}
			$this->trackException($e);
		}
		return($return);
	}

	function insertCopyrightInfo($idObj,$accessibility,$owner,$creationDate)
	{
		$return = NULL;
		try
		{
			if(is_int($idObj))
			{
				if(apiPHP::my_is_int($accessibility))
				{
			  	if(is_string($owner))
				{
					if(is_string($creationDate))
					{
						if(!$this->transactionInCourse)
						{
							$this->dbh->beginTransaction();
						}
						$query = "SELECT copyright_mod.copyright_info_i($idObj";
						$query .= ",'$owner'";
					       	//add embargo duration to creation date
					       	$arrayCreationDate = explode("/",$creationDate);
					       	$day = $arrayCreationDate[0];
					       	$month = $arrayCreationDate[1];
					       	$year = intval($arrayCreationDate[2]) + intval($accessibility);
						$embargoEnd = "$day/$month/$year";
						$query .= ",'$embargoEnd'";
						$query .= ") AS id_obj;";
						$arrayQuery = $this->get($query);
						if(!is_null($arrayQuery[0]["id_obj"]))
						{
							$return = intval($arrayQuery[0]["id_obj"]);
						}
						else
						{
							throw new Exception("Insertion info of copyright for object $idObj failed.");
						}
						if(!$this->transactionInCourse)
						{
							$this->dbh->commit();
						}
					}
					else
					{
						throw new Exception("Wrong parameter creation $creationDate given.");
					}
				}
				else
				{
					throw new Exception("Wrong parameter owner $owner given.");
				}
				}
				else
				{
					throw new Exception("Wrong parameter accessibility $accessibility given.");
				}
		      }
			else
			{
				throw new Exception("Wrong parameter idObj $idObj given.");
			}
		}
		catch(Exception $e)
		{
			if(!$this->transactionInCourse)
			{
				$this->dbh->rollBack();
			}
			$this->trackException($e);
		}
		return($return);
	}


	/*
	****************             ***************
	**************** MODULE META ***************
	****************             ***************
	*/

	/**
	 * DEPRECATED
	 * Select metadata type identifier
	 * @param string $nameMeta
	 * @throws Exception
	 * @return Ambigous <NULL, integer>
	 */
	function selectIdNameMeta($nameMeta)
	{
		$idMeta = NULL;
		try
		{
			if(is_string($nameMeta))
			{
				$arrayParam = array($namePara);
				$arrayQuery = $this->exec($this->queryNameParameterPrepared, $arrayParam);
				if(count($arrayQuery)==1)
				{
					$idPara = intval($arrayQuery[0]["id_column"]);
				}
				else
				{
					if(count($arrayQuery)==0)
					{
						//						throw new Exception("No colonne $namePara in database.");
					}
					else
					{
						throw new Exception("More than one colonnes meta $nameMeta in database.");
					}
				}
			}
			else
			{
				throw new Exception("Wrong parameter nameMeta $nameMeta given."); 
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idMeta);
	}

	/**
	 *
	 * Medata insertion with value associated
	 * @param int $idReleve
	 * @param string $name
	 * @param string $value
	 * @param string $typeSql
	 * @param string $unit
	 * @throws Exception
	 */
	function insertMetadata($nameMeta,$idObj,$value)
	{
		$return = NULL;
		try
		{
			if(is_int($idObj))
			{
				if(is_string($nameMeta))
				{
					if(is_string($value))
					{
						//local transaction : No changing variable which indicate begin of the transaction, end of transaction at the end of function
						if(!$this->transactionInCourse)
						{
							$this->dbh->beginTransaction();
						}
						$arrayQuery = $this->get("SELECT metadata_mod.insert_metadata_rel(metadata_mod.select_metadata_type('$nameMeta'),$idObj,'$value') AS id_meta_rel;");
						if(!is_null($arrayQuery[0]["id_meta_rel"]))
						{
							$return = intval($arrayQuery[0]["id_meta_rel"]);
						}
						else
						{
							throw new Exception("Insertion of metadata $nameMeta for object $idObj failed.");
						}
						if(!$this->transactionInCourse)
						{
							$this->dbh->commit();
						}
					}
					else
					{
						throw new Exception("Wrong parameter value $value given."); 
					}
				}
				else
				{
					throw new Exception("Wrong parameter nameMeta $nameMeta given."); 
				}
			}
			else
			{
				throw new Exception("Wrong parameter idObj $idObj given."); 
			}
		}
		catch(Exception $e)
		{
			//if(!$this->transactionInCourse)
			//{
			//	$this->dbh->rollBack();
			//}
			$this->trackException($e);
		}
		return($return);
	}
	
	/**
	 * 
	 * @param unknown_type $idObject
	 * @param unknown_type $nameMeta
	 * @throws Exception
	 * @return Ambigous <NULL, number>
	 */
	function selectObjMetadata($idObject,$nameMeta)
	{
		$valueMeta = NULL;
		try
		{
			if(is_integer($idObject))
			{
				if(is_string($nameMeta))
				{
					$query = "SELECT metadata_value as value_meta ".
							"FROM metadata_mod.metadata ".
								"JOIN metadata_mod.metadata_type USING(metadata_type_id) ".
							"WHERE global_identifier_id=$idObject ".
								"AND metadata_type_name='$nameMeta';";
					$arrayQuery = $this->get($query);
					if(!is_null($arrayQuery[0]["value_meta"]))
					{
						if(count($arrayQuery)==1)
						{
							$valueMeta = $arrayQuery[0]["value_meta"];
						}
						else
						{
							$valueMeta = "";
							foreach($arrayQuery as $row)
							{
								$valueMeta .= $row["value_meta"]."; ";
							}
							$valueMeta = substr($valueMeta,0,-2);
						}
					}
					else
					{
					}
				}
				else
				{
					throw new Exception("Wrong parameter nameMeta $nameMeta given."); 
				}
			}
			else
			{
				throw new Exception("Wrong parameter idObject $idObject given."); 
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($valueMeta);
	}

	/*
	****************             ***************
	**************** MODULE SITE ***************
	****************             ***************
	*/
	
	 /**
         *
         * Delete data associated to a geo entity by its name and type
		 *	(measures and relationship)
         * @param string $datasetName
         * @param string $senderName
         * @param string $creationDate
         * @throws Exception
         */
        function deleteGeoData($datasetName,$senderName,$creationDate)
        {
                $return = NULL;
                try
                {
                        if(is_string($datasetName))
                        {
                                if(is_string($senderName))
                                {
                                        if(is_string($creationDate))
                                        {
                                                //insertion by fonction
                                                if($this->typeInsert=='functionSQL')
                                                {
                                                        $query = "SELECT
														site_mod.delete_geographical_entity_measures('$datasetName','$senderName','$creationDate')
														AS return;";
                                                        $arrayQuery = $this->get($query);
                                                        if(intval($arrayQuery[0]["return"])!==1)
                                                        {
                                                                throw new Exception("Delete geo data for $nameGeo de type
																$typeGeo with metadata $senderName and $creationDate failed.");
                                                        }
                                                }
                                        }
                                        else
                                        {
                                                throw new Exception("Wrong argument creationDate $creationDate
												given.");
                                        }
                                }
                                else
                                {
                                        throw new Exception("Wrong argument senderName $senderName given.");
                                }
                        }
                        else
                        {
                                throw new Exception("Wrong argument datasetName $datasetName given.");
                        }
                }
                catch(Exception $e)
                {
                        $this->trackException($e);
                }
                return($return);
        }

	/**
	 *
	 * Insertion of geographical entity
	 * @param string $nameGeo
	 * @param string $typeGeo
	 * @throws Exception
	 * @return integer
	 */
	function insertGeo($nameGeo,$typeGeo)
	{
		$return = NULL;
		try
		{
			if(is_string($nameGeo))
			{
				if(is_string($typeGeo))
				{
					//insertion by function
					if($this->typeInsert=='functionSQL')
					{
						$arrayQuery = $this->get("SELECT site_mod.geographical_entity_s_i((SELECT site_mod.select_type_geo('$typeGeo') AS id_type),'$nameGeo') AS id_geo;");
						if(!is_null($arrayQuery[0]["id_geo"]))
						{
							$return = intval($arrayQuery[0]["id_geo"]);
						}
						else
						{
							throw new Exception("Insertion of geogrpahical entity $nameGeo with type $typeGeo failed.");
						}
					}
				}
				else
				{
					throw new Exception("Wrong argument nameGeo $nameGeo given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument typeGeo $typeGeo given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}

	/**
	 * 
	 * Insertion of localised geographical entity
	 * @param object $myGeoPlot
	 * @throws Exception
	 */
	function insertGeoReferenced($nameGeo, $typeGeo, $longitude, $latitude, $refGeo)
	{
		$return = NULL;
		try
		{
/*			$nameGeo = $myGeoPlot->get("name_geo");
			$typeGeo = $myGeoPlot->get("type_geo");
			$longitude = $myGeoPlot->get("longitude");
			$latitude = $myGeoPlot->get("latitude");
			$refGeo = $myGeoPlot->get("ref_geo");
*/
//			if(is_a($myGeoPlot,"GeoEntity"))
//			{
				if(is_string($nameGeo))
				{
					if(is_string($typeGeo))
					{
						//insertion by function
						if($this->typeInsert=='functionSQL')
						{
// 							if($myGeoPlot->get("delete")===TRUE)
// 							{
// 								$arrayQuery = $this->get("SELECT site_mod.geographical_entity_d(site_mod.select_type_geo('$typeGeo'),'$nameGeo');");
// 							}
							$arrayQuery = $this->get("SELECT site_mod.geographical_entity_location_s_i(site_mod.select_type_geo('$typeGeo'),'$nameGeo','$longitude','$latitude',site_mod.georeferential_s_i('$refGeo')) AS id_plot;");
							if(!is_null($arrayQuery[0]["id_plot"]))
							{
								$return = intval($arrayQuery[0]["id_plot"]);
							}
							else
							{
								throw new Exception("Insertion of localised geographical entity $nameGeo of type $typeGeo failed.");
							}
						}
					}
					else
					{
						throw new Exception("Wrong argument nameGeo $nameGeo given.");
					}
				}
				else
				{
					throw new Exception("Wrong argument typeGeo $typeGeo given.");
				}
//			}
//			else
//			{
//				throw new Exception("Wrong argument GeoEntity object given.");
//			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}

	function selectMesureGeo($idPlot,$typeMesure,$arrayDate,$arrayMeta)
	{
		$idMeasure = NULL;
		try
		{
			if(is_integer($idPlot))
			{
				if(is_string($typeMesure))
				{
					$query = "SELECT geographical_entity_measure_value_id as geographical_entity_measure_value_id ".
						"FROM site_mod.geographical_entity_measure_value ".
							"JOIN metadata_mod.metadata ON(global_identifier_id=geographical_entity_measure_value_id) ".
						"WHERE geographical_entity_id=$idPlot ".
							"AND geographical_entity_measure_id=site_mod.select_mesure_geo('$typeMesure') ";
					//better to be an object to test interval structure
					if(is_array($arrayDate))
					{
						//start
						if(is_array($arrayDate[0]))
						{
							$typeDate = 'sampling_date_start';
							$arrayDateStart = $arrayDate[0];
							foreach($arrayDateStart as $date=>$value)
							{
								$query .= "AND site_mod.select_geographical_entity_measure_date(geographical_entity_measure_value_id,date_mod.select_date_entite('$date'),date_mod.select_date_type('$typeDate'))='$value' ";
							}
						}
						//end
						if(is_array($arrayDate[1]))
						{
							$typeDate = 'sampling_date_end';
							$arrayDateEnd = $arrayDate[1];
							foreach($arrayDateEnd as $date=>$value)
							{
								$query .= "AND site_mod.select_geographical_entity_measure_date(geographical_entity_measure_value_id,date_mod.select_date_entite('$date'),date_mod.select_date_type('$typeDate'))='$value' ";
							}
						}
					}
					if(is_array($arrayMeta))
					{
						foreach($arrayMeta as $meta=>$value)
						{
							$query .= "AND metadata_mod.select_metadata_value(metadata_mod.select_metadata_type('$meta'),geographical_entity_measure_value_id)='$value' ";
						}
					}
					$query .= ';';
					$arrayQuery = $this->get($query);
					if(!is_null($arrayQuery[0]))
					{
						$idMeasure = intval($arrayQuery[0]["geographical_entity_measure_value_id"]);
					}
				}
				else
				{
					throw new Exception("Wrong argument typeMesure $typeMesure given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument idPlot $idPlot given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idMeasure);
	}
	
	/**
	 *
	 * Insertion of measure linked to the parcel
	 * @param integer $idPlot
	 * @param string $typeMesure
	 * @param string $mesure
	 * @throws Exception
	 * @return Ambigous <NULL, number>
	 */
	function insertMesureGeo($idPlot,$typeMesure,$mesure)
	{
		$return = NULL;
		try
		{
			if(is_integer($idPlot))
			{
				if(is_string($typeMesure))
				{
					if(is_string($mesure))
					{
						//if(!$this->transactionInCourse)
						//{
						//	$this->dbh->beginTransaction();
						//}
						$idMeasure = $this->selectTypeMeasureGeoId($typeMesure);
						$typeSqlValue = $this->selectMeasureGeoSqlType($idMeasure);
						if($this->checkTypeValue($mesure, $typeSqlValue))
						{

							$arrayQuery = $this->get("SELECT site_mod.insert_geographical_entity_measure_geo($idPlot,site_mod.select_mesure_geo('$typeMesure'),'$mesure') AS measure_geo_id;");
							if(!is_null($arrayQuery[0]["measure_geo_id"]))
							{
								$return = intval($arrayQuery[0]["measure_geo_id"]);
							}
							else
							{
								throw new Exception("Insertion of geographical measure $typeMesure for plot $idPlot failed.");
							}
							//if(!$this->transactionInCourse)
							//{
							//	$this->dbh->commit();
							//}
						}
						else
						{
							switch ($typeSqlValue) 
							{
								case 'float8':
									throw new Exception("Error: the measure \"$typeMesure\" of value \"$mesure\" is not a number. Please correct, re-save and re-insert.");
									break;

								case 'int4':
									throw new Exception("Error: the measure \"$typeMesure\" of value \"$mesure\" is not a integer number. Please correct, re-save and re-insert.");
									break;								
								
								default:
									throw new Exception("Error: the measure \"$typeMesure\" of value \"$mesure\" is not a $typeSqlValue. Please correct, re-save and re-insert.");
									break;
							}	
						}
					}
					else
					{
						throw new Exception("Wrong argument mesure $mesure given.");
					}
				}
				else
				{
					throw new Exception("Wrong argument typeMesure $typeMesure given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument idPlot $idPlot given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}
	
	/**
	 *
	 * Insertion of measure date linked to a measure geo
	 * @param integer $idMeasureValue
	 * @param string $date
	 * @param string $typeDate
	 * @param string $functionDate
	 * @throws Exception
	 * @return Ambigous <NULL, number>
	 */
	function insertMesureGeoDate($idMeasureValue,$date,$typeDate,$functionDate)
	{
		$return = NULL;
		try
		{
			if(is_integer($idMeasureValue))
			{
				if(is_string($date))
				{
					if(is_string($typeDate))
					{
						if(is_string($functionDate))
						{
							//if(!$this->transactionInCourse)
							//{
							//	$this->dbh->beginTransaction();
							//}
							$arrayQuery = $this->get("SELECT site_mod.geographical_entity_measure_geo_date_s_i_u($idMeasureValue,".
								"date_mod.select_date_entite('$typeDate'),date_mod.select_date_type('$functionDate'),'$date') AS measure_geo_id;");
								if(!is_null($arrayQuery[0]["measure_geo_id"]))
								{
									$return = intval($arrayQuery[0]["measure_geo_id"]);
								}
								else
								{
									throw new Exception("Insertion of geographical measure $typeMesure for plot $idPlot failed.");
								}
							//	if(!$this->transactionInCourse)
							//	{
							//		$this->dbh->commit();
							//	}
						}
						else
						{
							throw new Exception("Wrong argument mesure $functionDate given.");
						}
					}
					else
					{
						throw new Exception("Wrong argument mesure $typeDate given.");
					}
				}
				else
				{
					throw new Exception("Wrong argument typeMesure $date given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument idPlot $idMeasureValue given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}
	
	function selectTypeMeasureGeoId($nameMes)
	{
		$idMes = NULL;
		try
		{
			if(is_string($nameMes))
			{
				$query = "SELECT site_mod.select_mesure_geo('$nameMes') AS id_measure;";
				$arrayQuery = $this->get($query);
				if(!is_null($arrayQuery[0]["id_measure"]))
				{
					$idMes = intval($arrayQuery[0]["id_measure"]);
				}
				else
				{
					throw new Exception("No measure $nameMes of type $typeMes in database.");
				}
			}
			else
			{
				throw new Exception("Wrong type for name measure $nameMes.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idMes);
	}
	
	function selectMeasureGeoSqlType($idMes)
	{
		$value = NULL;
		try
		{
			if(is_int($idMes))
			{
				$query = "SELECT site_mod.select_sql_type_measure_geo($idMes) AS sql_type;";
				$arrayQuery = $this->get($query);
				if(!is_null($arrayQuery[0]["sql_type"]))
				{
					$value = $arrayQuery[0]["sql_type"];
				}
				else
				{
					throw new Exception("No sql type for measure $idMes.");
				}
			}
			else
			{
				throw new Exception("Wrong type for id measure $idMes.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($value);
	}

	/**
	 * 
	 * Insert relationship between geographical entities
	 * @param integer $idGeo
	 * @param integer $idGeoSon
	 * @throws Exception
	 * @return Ambigous <NULL, number>
	 */
	function insertGeoInclusion($idGeo,$idGeoSon)
	{
		$return = NULL;
		try
		{
			if(is_integer($idGeo))
			{
				if(is_integer($idGeoSon))
				{
					//insertion from function
					if($this->typeInsert=='functionSQL')
					{
						$arrayQuery = $this->get("SELECT site_mod.insert_geo_entity_rel($idGeo,$idGeoSon);");
						if(count($arrayQuery)==1)
						{
							$return = 1;
						}
						else
						{
							if(count($arrayQuery)==0)
							{
							}
							else
							{
							}
						}
					}
				}
				else
				{
					throw new Exception("Wrong argument idGeoSon $idGeoSon given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument idGeo $idGeo given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}

	/**
	 *
	 * Select Db identifier of geographical entity from its name and its type
	 * @param string $nameGeo
	 * @param string $typeGeo
	 * @throws Exception
	 * @return Ambigous <NULL, number>
	 */
	function selectIdGeo($nameGeo,$typeGeo)
	{
		$return = NULL;
		try
		{
			if(is_string($nameGeo))
			{
				if(is_string($typeGeo))
				{
					$arrayQuery = $this->get("SELECT site_mod.select_geo_entity(site_mod.select_type_geo('$typeGeo'),'$nameGeo') AS id_geo;");
					if(!is_null($arrayQuery[0]["id_geo"]))
					{
						$return = intval($arrayQuery[0]["id_geo"]);
					}
					else
					{
						throw new Exception("No $nameGeo of type $typeGeo in database.");
					}
				}
				else
				{
					throw new Exception("Wrong parameter typeGeo $typeGeo given."); 
				}
			}
			else
			{
				throw new Exception("Wrong parameter nameGeo $nameGeo given."); 
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}
	
	/**
	 *
	 * Select name of geographical entity from its Db indentifier
	 * @param integer $idGeo
	 * @throws Exception
	 * @return Ambigous <NULL, number>
	 */
	function selectNameGeo($idGeo)
	{
		$nameGeo = NULL;
		try
		{
			if(is_integer($idGeo))
			{
				$arrayQuery = $this->get("SELECT site_mod.select_geo_entity_name($idGeo) AS name_geo;");
				if(!is_null($arrayQuery[0]["name_geo"]))
				{
					$nameGeo = $arrayQuery[0]["name_geo"];
				}
				else
				{
					throw new Exception("No geography with id $idGeo in database.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter idGeo $idGeo given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($nameGeo);
	}
	
	function selectLocationGeo($idGeo)
	{
		$arrayLocation = NULL;
		try
		{
			if(is_integer($idGeo))
			{
				$arrayQuery = $this->get("SELECT x AS latitude, y AS longitude FROM site_mod.geographical_entity_location WHERE geographical_entity_id=$idGeo;");
				if(!is_null($arrayQuery[0]["latitude"])&&!is_null($arrayQuery[0]["longitude"]))
				{
					$arrayLocation = $arrayQuery[0];
				}
				else
				{
					throw new Exception("No location with id $idGeo in database.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter idGeo $idGeo given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayLocation);
	}

	/**
	 * Select database if of a geo type from its name
	 * @param string $nameTypeGeo
	 * @throws Exception
	 * @return integer
	 */
	function selectIdTypeGeo($nameTypeGeo)
	{
		$idTypeGeo = NULL;
		try
		{
			if(is_string($nameTypeGeo))
			{
				$arrayQuery = $this->get("SELECT site_mod.select_type_geo('$nameTypeGeo') AS id_type_geo;");
				if(!is_null($arrayQuery[0]["id_type_geo"]))
				{
					$idTypeGeo = $arrayQuery[0]["id_type_geo"];
				}
				else
				{
					throw new Exception("No geography type $nameTypeGeo in database.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter idGeo $idGeo given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idTypeGeo);
	}

	function selectAllIdGeoParentByName($idGeo,$nameType=NULL)
	{
		$arrayIdGeo = NULL;
		try
		{
			if(is_integer($idGeo))
			{
				if(is_string($nameType)||is_null($nameType))
				{
					if(is_null($nameType))
					{
						$query = "SELECT site_mod.select_all_geo_parent($idGeo,NULL) AS id_geo;";
					}
					else
					{
						$query = "SELECT site_mod.select_all_geo_parent($idGeo,site_mod.select_type_geo('$nameType')) AS id_geo;";
					}
					$arrayQuery = $this->get($query);
					if(!is_null($arrayQuery[0]["id_geo"]))
					{
						$arrayIdGeo = $arrayQuery;
					}
					else
					{
//					      throw new Exception("No parent of type $idType with for geo id $idGeo in database.");
					}
				}
				else
				{
					throw new Exception("Wrong parameter nameType $nameType given.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter idGeo $idGeo given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayIdGeo);
	}


	/**
	 * Select all db id of a geo entity with a certain type if wanted
	 * @param integer $idGeo
	 * @param integer $idType
	 * @throws Exception
	 * @return array of integer
	 */
	function selectAllIdGeoParent($idGeo,$idType=NULL)
	{
		$arrayIdGeo = NULL;
		try
		{
			if(is_integer($idGeo))
			{
				if(is_int($idType)||is_null($idType))
				{
					if(is_null($idType))
					{
						$query = "SELECT site_mod.select_all_geo_parent($idGeo,NULL) AS id_geo;";
					}
					else
					{
						$query = "SELECT site_mod.select_all_geo_parent($idGeo,$idType) AS id_geo;";
					}
					$arrayQuery = $this->get($query);
					if(!is_null($arrayQuery))
					{
						$arrayIdGeo = $arrayQuery;
					}
					else
					{
//						throw new Exception("No parent of type $idType with for geo id $idGeo in database.");
					}
				}
				else
				{
					throw new Exception("Wrong parameter idType $idType given.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter idGeo $idGeo given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayIdGeo);
	}
	
	/**
	 * Select a measure geo by year
	 * @param integer $idGeo
	 * @param string $measure
	 */
	function selectAllMeasureGeo($idGeo,$measure)
	{
		$arrayMes = NULL;
		try
		{
			if(is_integer($idGeo))
			{
				if(is_string($measure))
				{
					$query = "SELECT geo_year,geo_measure FROM site_mod.select_all_year_measure_geo($idGeo,site_mod.select_mesure_geo('$measure'));";
					$arrayQuery = $this->get($query);
					if(!is_null($arrayQuery)&&count($arrayQuery)>0)
					{
						$arrayMes = $arrayQuery;
					}
					else
					{
					}
				}
				else
				{
					throw new Exception("Wrong parameter measure $measure given.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter idGeo $idGeo given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayMes);
	}
	
	/*
	****************                   ***************
	**************** MODULE EXPERIENCE ***************
	****************                   ***************
	*/

	function selectSampleSampling($sampleId)
	{
		$idSampling = NULL;
		try
		{
			if(is_int($sampleId))
			{
				$query = "SELECT sampling_id AS sampling_id FROM experience_mod.sample WHERE sample_id=$sampleId;";
				$arrayQuery = $this->get($query);
				{
					$idSampling = $arrayQuery[0]["sampling_id"];
				}
			}
			else
			{
				throw new Exception("Wrong parameter samlpeId $sampleId given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idSampling);
	}


	/**
	 * Select geographical entity id associated directly with sample
	 * @param integer $sampleId
	 * @throws Exception
	 */
	function selectSampleGeo($sampleId)
	{
		$idPlot = NULL;
		try
		{
			if(is_int($sampleId))
			{
				$query = "SELECT geographical_entity_id AS id_plot ".
						"FROM experience_mod.sample ".
						"WHERE sample_id = $sampleId;";
				$arrayQuery = $this->get($query);
				if(!is_null($arrayQuery[0]["id_plot"]))
				{
					$idPlot = intval($arrayQuery[0]["id_plot"]);
				}
			}
			else
			{
				throw new Exception("Wrong parameter sampleId $sampleId given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idPlot);
	}

	/**
	 * Return type(s) of sampling
	 * @param integer $samplingId
	 * @throws Exception
	 * @return varchar
	 */
	function selectSamplingType($samplingId)
	{
		$valueType = NULL;
		try
		{
			if(is_int($sampleId))
			{
				$query = "SELECT experience_mod.select_sampling_type($samplingId) AS name_type;";
				$arrayQuery = $this->get($query);
				{
					if(!is_null($arrayQuery[0]["name_type"]))
					{
						$valueType = "";
						foreach($arrayQuery as $row)
						{
							$valueType .= $row["name_type"];
						}
					}
					else
					{
						throw new Exception("samplingId $samplingId has type problem.");
					}
				}
			}
			else
			{
				throw new Exception("Wrong parameter sampleId $sampleId given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($valueType);
	}

	/**
	 * Return type of sample
	 * @param integer $sampleId
	 * @throws Exception
	 * @return varchar 
	 */
	function selectSampleType($sampleId)
	{
		$valueType = NULL;
		try
		{
			if(is_int($sampleId))
			{
				$query = "SELECT sampling_type_name AS name_type FROM experience_mod.sample JOIN experience_mod.sampling_type USING(sampling_type_id) WHERE sample_id=$sampleId;";
				$arrayQuery = $this->get($query);
				{
					$valueType = $arrayQuery[0]["name_type"];
				}
			}
			else
			{
				throw new Exception("Wrong parameter sampleId $sampleId given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($valueType);
	}

	function selectAllSamplePos()
	{
		$arraySample = NULL;
		try
		{
			$query = "SELECT sample_id AS id_releve, ST_AsGeoJSON(ST_GeomFromText('POINT('||dms2dd(x)||' '||dms2dd(y)||')',4326)),sampling_type_id AS id_type FROM site_mod.geographical_entity_location JOIN experience_mod.sample USING(geographical_entity_id);";
			$arrayQuery = $this->get($query);
			$arraySample = $arrayQuery;
			if(is_null($arrayQuery[0]["id_releve"]))
			{
				throw new Exception("No sample in database.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arraySample);
	}

	/**
	*
	* Select sampling protocol from its name
	* @param string $namePrt
	* @throws Exception
	* @return integer
	*/
	function selectIdStrategy($nameStr)
	{
		$idStr = NULL;
		try
		{
			if(is_string($nameStr))
			{
				$arrayQuery = $this->get("SELECT experience_mod.select_strategie('$nameStr') AS id_str;");
				$idStr = $arrayQuery[0]["id_str"];
				if(is_null($idStr))
				{
					throw new Exception("No sampling strategy '$nameStr' in database.");
				}
				else
				{
					$idStr = $idStr;
				}
			}
			else
			{
				throw new Exception("Wrong parameter nameStr $nameStr given."); 
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idStr);
	}
	
	/**
	 * 
	 * Selection of a sampling protocol froom its name
	 * @param string $namePrt
	 * @throws Exception
	 * @return integer
	 */
	function selectIdProtocol($namePrt)
	{
		$idPrt = NULL;
		try
		{
			if(is_string($namePrt))
			{
				$query = "SELECT experience_mod.select_protocole('$namePrt') AS id_prt;";
				$arrayQuery = $this->get($query);
				$idPrt = $arrayQuery[0]["id_prt"];
				if(is_null($idPrt))
				{
					throw new Exception("The sampling_protocol: '$namePrt' is outside the allowable range or vocabulary. Please correct, re-save and re-insert. See the Universe in the original template files for the list of vocabulary or range of values allowed in this field.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter namePrt $namePrt given."); 
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idPrt);
	}
	
	
	/**
	 *
	 * Selection of abund's DB identifier from its species identifier and its experiment identifier
	 * @param int $idReleve
	 * @param int $idSpecies
	 * @throws Exception
	 * @return Ambigous <NULL, number>
	 */
	function selectIdAbund($idReleve,$idSpecies)
	{
		$idAbund = NULL;
		try
		{
			if(is_int($idReleve))
			{
				if(is_int($idSpecies))
				{
					$arrayParam = array($pub);
					$arrayQuery = $this->exec($this->queryAbundPrepared, $arrayParam);
					if(!is_null($arrayQuery[0]["id_abund"]))
					{
						$idAbund = intval($arrayQuery[0]["id_abund"]);
					}
					else
					{
						throw new Exception("No abund linked to species $idSpecies and experiment $idReleve in database.");
					}
				}
				else
				{
					throw new Exception("Wrong parameter idSpecies $idSpecies given."); 
				}
			}
			else
			{
				throw new Exception("Wrong parameter idReleve $idReleve given."); 
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idAbund);
	}

	/**
	 *
	 * Abund insertion
	 * @param int $idReleve
	 * @param numeric $value
	 * @param int $idSpecies
	 * @throws Exception
	 * @return Ambigous <NULL, number>
	 */
	function insertAbund($idSample,$idSpecies,$idType,$value,$lifeStage=NULL)
	{
		$return = NULL;
		try
		{
			if(is_int($idSample))
			{
				if(is_numeric($value))
				{
					if(is_int($idSpecies))
					{
						if(is_int($idType))
						{
							if(!$this->transactionInCourse)
							{
								$this->dbh->beginTransaction();
							}
							if($lifeStage==NULL)
							{
								$arrayQuery = $this->get("SELECT experience_mod.observation_s_i_u($idSpecies,$idSample,$idType,$value,NULL);");
							}else{
								$arrayQuery = $this->get("SELECT experience_mod.observation_s_i_u($idSpecies,$idSample,$idType,$value,'$lifeStage');");
							}
							
							//TODO check its ok
							if(count($arrayQuery)==1)
							{
								$return = 1;
							}
							if(!$this->transactionInCourse)
							{
								$this->dbh->commit();
							}
						}
						else
						{
							throw new Exception("Wrong argument idType $idType given.");
						}
					}
					else
					{
						throw new Exception("Wrong argument idSpecies $idSpecies given.");
					}
				}
				else
				{
					throw new Exception("Wrong argument value $value given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument idSample $idSample given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}

	/**
	 * 
	 * Select observation type from its name
	 * @param string $nameType
	 * @throws Exception
	 * @return integer
	 */
	function selectIdTypeObservation($nameType)
	{
		$idType = NULL;
		try
		{
			if(is_string($nameType))
			{
				$arrayQuery = $this->get("SELECT experience_mod.select_type_observation('$nameType') AS id_type;");
				$idType = $arrayQuery[0]["id_type"];
				if(is_null($idType))
				{
					throw new Exception("No observation type $nameType in database.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter nameType $nameType given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idType);
	}

	/**
	 *
	 * Sampling insertion
	 * @param SamplingObject $mySampling
	 * @throws Exception
	 */
	function insertSampling($mySampling,$idSrc=NULL)
	{
		$return = NULL;
		try
		{
			if(is_a($mySampling,"Sampling"))
			{
				$namePrt = $mySampling->get("protocol");
				$nameStr = $mySampling->get("strategy");
				$nameSampling = $mySampling->get("name_sampling");
				if(is_string($namePrt))
				{
					if(is_string($nameStr))
					{
						if(!$this->transactionInCourse)
						{
							$this->dbh->beginTransaction();
						}
						if(!is_null($idSrc))
						{
							if(is_integer($idSrc))
							{
								//insertion of source reference
								$query1 = "SELECT reference_mod.source_ref_s_i($idSrc,'$nameSampling') AS id_ref;";
								$arrayQuery = $this->get($query1);
								if(!is_null($arrayQuery[0]["id_ref"]))
								{
									$idRef = intval($arrayQuery[0]["id_ref"]);
									if(!is_null($idPrt = $this->selectIdProtocol($namePrt)))
									{
										if(!is_null($idStr = $this->selectIdStrategy($nameStr)))
										{
											$query = "SELECT experience_mod.sampling_s_i_u($idRef,$idPrt,".
												"experience_mod.select_strategie('$nameStr')) AS id_ech;";
										}
										else
										{
											throw new Exception("Strategy $nameStr insertion failed.");
										}
									}
									else
									{
										throw new Exception("Protocol $namePrt insertion failed.");
									}
								}
								else
								{
									throw new Exception("Reference insertion failed.");
								}
							}
							else
							{
								throw new Exception("Wrong argument idSrc $idSrc given.");
							}
						}
						else
						{
							$query = "SELECT experience_mod.sampling_i(experience_mod.select_protocole('$namePrt'),".
								"experience_mod.select_strategie('$nameStr')) AS id_ech;";
						}
						$arrayQuery = $this->get($query);
						if(!is_null($arrayQuery[0]["id_ech"]))
						{
							$return = intval($arrayQuery[0]["id_ech"]);
						}
						else
						{
							throw new Exception("Sampling $nameSampling insertion failed.");
						}
						if(!$this->transactionInCourse)
						{
							$this->dbh->commit();
						}
					}
					else
					{
						throw new Exception("Wrong argument nameStr $nameStr.");
					}
				}
				else
				{
					throw new Exception("Wrong argument namePrt $namePrt.");
				}
			}
			else
			{
				throw new Exception("Wrong argument sampling given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}

	/**
	 *
	 * Insertion of sample
	 * @param SampleObject $mySample
	 * @param integer $idSrc
	 * @throws Exception
	 * @return Ambigous <NULL, integer>
	 */
	function insertSample($mySample,$idSrc)
	{
		$return = NULL;
		try
		{
			if(is_a($mySample,"Sample"))
			{
				if(is_integer($idSrc))
				{
					$mySampling = $mySample->get("sampling");
					$myGeo = $mySample->get("geo_entity");
					$dayS = $mySample->get("day_start");
					$monthS = $mySample->get("month_start");
					$yearS = $mySample->get("year_start");
					$dayE = $mySample->get("day_end");
					$monthE = $mySample->get("month_end");
					$yearE = $mySample->get("year_end");
					$sampleType = $mySample->get("sample_type");
					$sampleName = $mySample->get("sample_name");
					//case where there are replicats
					$sampleReplicateName = $mySample->get("sample_replicate_name");
					if(!is_null($sampleReplicateName))
					{
						$sampleName .= "@$sampleReplicateName";
					}	
					$arrayMesure = $mySample->get("array_mesure_sample");
					if(is_a($mySampling,"Sampling"))
					{
						$idSampling = $mySampling->get("id_db");

						if(is_a($myGeo,"GeographicalEntity"))
						{
							if(is_null($idGeo=$this->selectIdGeo($myGeo->getValue(),$myGeo->getType())))
							{
								throw new Exception("Geographical entity problem.");
							}
							if(is_string($sampleType))
							{
								if(is_string($sampleName))
								{
									if(!$this->transactionInCourse)
									{
										$this->dbh->beginTransaction();
									}
									//insertion of reference source
									$arrayQuery = $this->get("SELECT reference_mod.source_ref_s_i($idSrc,'$sampleName') AS id_ref;");
									if(!is_null($arrayQuery[0]["id_ref"]))
									{
										$idRef = intval($arrayQuery[0]["id_ref"]);
									}
									else
									{
										throw new Exception("Insertion of reference failed.");
									}
									$arrayQuery = $this->get("SELECT experience_mod.sample_s_i_u($idRef,experience_mod.select_type_sample('$sampleType'),$idSampling,$idGeo) AS id_ech;");
									if(!is_null($arrayQuery[0]["id_ech"]))
									{
										$return = intval($arrayQuery[0]["id_ech"]);
										$idSample = $return;
									}
									else
									{
										throw new Exception("Insertion of sample $sampleName failed.");
									}
									//association sample/ref
									$arrayQuery = $this->get("SELECT reference_mod.insert_objet_ref($idSample,$idRef) AS id_ech;");
									if(is_null($arrayQuery[0]["id_ech"]))
									{
										$return = NULL;
										throw new Exception("Insertion of relationship object/reference failed.");
									}
									////dates insertion
									//TODO function or loop
									//day
									$nameDate = 'day';
									$value = $dayS;
									$typeDate = 'sampling_date_start';
									if(!is_null($value))
									{
										$arrayQuery = $this->get("SELECT experience_mod.sample_date_s_i_u(date_mod.select_date_entite('$nameDate'),$idSample,date_mod.select_date_type('$typeDate'),'$value') AS my_row;");
									}
									$value = $dayE;
									$typeDate = 'sampling_date_end';
									if(!is_null($value))
									{
										$arrayQuery = $this->get("SELECT experience_mod.sample_date_s_i_u(date_mod.select_date_entite('$nameDate'),$idSample,date_mod.select_date_type('$typeDate'),'$value') AS my_row;");
									}
									//month
									$nameDate = 'month';
									$value = $monthS;
									$typeDate = 'sampling_date_start';
									if(!is_null($value))
									{
										$arrayQuery = $this->get("SELECT experience_mod.sample_date_s_i_u(date_mod.select_date_entite('$nameDate'),$idSample,date_mod.select_date_type('$typeDate'),'$value') AS my_row;");
										//seasonality
										$nameDateSeason = 'season';
										$typeDateSeason = 'sampling_description';
										$seasonOk = FALSE;
										switch ($value)
										{
											case $value=='1'||$value=='01'||$value=='2'||$value=='02'||$value=='3'||$value=='03':
												$valueSeason = 'winter';
												$seasonOk = TRUE;
												break;
											case $value=='4'||$value=='04'||$value=='5'||$value=='05'||$value=='6'||$value=='06':
												$valueSeason = 'spring';
												$seasonOk = TRUE;
												break;
											case $value=='7'||$value=='07'||$value=='8'||$value=='08'||$value=='9'||$value=='09':
												$valueSeason = 'summer';
												$seasonOk = TRUE;
												break;
											case $value=='10'||$value=='11'||$value=='12':
												$valueSeason = 'fall';
												$seasonOk = TRUE;
												break;
											default:
												print("Wrong format for month $monthS.");
												break;
										}
										if($seasonOk)
										{
											$arrayQuery = $this->get("SELECT experience_mod.sample_date_s_i_u(date_mod.select_date_entite('$nameDateSeason'),$idSample,date_mod.select_date_type('$typeDateSeason'),'$valueSeason') AS my_row;");
										}
									}
									$value = $monthE;
									$typeDate = 'sampling_date_end';
									if(!is_null($value))
									{
										$arrayQuery = $this->get("SELECT experience_mod.sample_date_s_i_u(date_mod.select_date_entite('$nameDate'),$idSample,date_mod.select_date_type('$typeDate'),'$value') AS my_row;");
									}
									//year
									$nameDate = 'year';
									$value = $yearS;
									$typeDate = 'sampling_date_start';
									if(!is_null($value))
									{
										$arrayQuery = $this->get("SELECT experience_mod.sample_date_s_i_u(date_mod.select_date_entite('$nameDate'),$idSample,date_mod.select_date_type('$typeDate'),'$value') AS my_row;");
									}
									$value = $yearE;
									$typeDate = 'sampling_date_end';
									if(!is_null($value))
									{
										$arrayQuery = $this->get("SELECT experience_mod.sample_date_s_i_u(date_mod.select_date_entite('$nameDate'),$idSample,date_mod.select_date_type('$typeDate'),'$value') AS my_row;");
									}
									if(!$this->transactionInCourse)
									{
										$this->dbh->commit();
									}
								}
								else
								{
									throw new Exception("Wrong argument sample name.");
								}
							}
							else
							{
								throw new Exception("Wrong argument sample type.");
							}
						}
						else
						{
							throw new Exception("Wrong argument geo entity.");
						}
					}
					else
					{
						throw new Exception("Wrong argument sampling.");
					}
				}
				else
				{
					throw new Exception("Wrong argument idSrc $idSrc given");
				}
			}
			else
			{
				throw new Exception("Wrong argument sample given");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}
	
	/**
	 * Select db id of sample type
	 * @param string $typeSample Sample type (soils, fauna)
	 * @throws Exception
	 * @return Ambigous <NULL, integer>
	 */
	function selectIdTypeSample($typeSample)
	{
		$return = NULL;
		try
		{
			if(is_string($typeSample))
			{
				$arrayQuery = $this->get("SELECT experience_mod.select_type_sample('$typeSample') AS id_type_sample;");
				if(!is_null($arrayQuery[0]["id_type_sample"]))
				{
					$return = intval($arrayQuery[0]["id_type_sample"]);
				}
				else
				{
					throw new Exception("No typeSample $typeSample in database.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter typeSample $typeSample given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}

	/**
	 * Select private sample from database
	 * @param array $arrayFilter Filter in where
	 * @param array $type Type of sample
	 * @throws Exception
	 */
	function selectSamplePrivate($type,$arrayFilter)
	{
		$arraySample = NULL;
		try
		{
			////variables
			//query construction
			$query = "";
			$columnPart = "SELECT ";
			$joinPart = "FROM ";
			$wherePart = "WHERE ";
			$orderPart = "ORDER BY ";
			if($type=='fauna')
			{
				//taxon to filter
				$arrayTaxo = $arrayFilter["taxo"];
				if(count($arrayTaxo)==0)
				{
					throw new Exception("No taxon chosen.");
				}
				//type observation to filter
				$arrayTypeObs = $arrayFilter["type_obs"];
				if(count($arrayTypeObs)==0)
				{
					throw new Exception("No type observation chosen.");
				}
				//years to filter
				$arrayYear = array();
				if(key_exists("year",$arrayFilter))
				{
					$arrayYear = $arrayFilter["year"];
				}
				////programmation side
				//internal id
				$columnPart .= "sampling_id, sample_id, observation_id, fauna.geographical_entity_id AS parcel_id, taxon_id, observation_value ";
				$columnPart .= ", experience_mod.select_sample_year(fauna.sample_id,date_mod.select_date_type('sampling_date_start')) AS sample_year ";
				////table to join
				$joinPart .= "experience_mod.sample AS fauna ".
						"JOIN experience_mod.sampling AS fauning USING(sampling_id) ".
						"JOIN experience_mod.observation USING(sample_id) ";
				////////where filter
				////needed
				$wherePart .= "fauna.sampling_type_id=experience_mod.select_type_sample('fauna') ";
				//taxon
				$wherePart .= " AND taxon_id IN(";
				$lastTaxo = array_pop($arrayTaxo);
				if(count($arrayTaxo)>0)
				{
					foreach($arrayTaxo as $taxo)
					{
						$wherePart .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$taxo') UNION ";
					}
				}
				$wherePart .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$lastTaxo')) AND ";
				//observation type
				$wherePart .= "observation_type_id IN(";
				$lastTypeObs = array_pop($arrayTypeObs);
				if(count($arrayTypeObs)>0)
				{
					foreach($arrayTypeObs as $typeObs)
					{
						$wherePart .= "experience_mod.select_type_observation('$typeObs'), ";
					}
				}
				$wherePart .= "experience_mod.select_type_observation('$lastTypeObs')) ";
				////facultative
				//year bounds
				if(count($arrayYear)>0)
				{
					$yearMin = $arrayYear["min"];
					$yearMax = $arrayYear["max"];
					$wherePart .= "AND experience_mod.select_sample_year(fauna.sample_id,date_mod.select_date_type('sampling_date_start')) BETWEEN $yearMin AND $yearMax ";
				}
				////order part
				$orderPart .= "sample_year, fauna.geographical_entity_id, fauna.sampling_id, fauna.sample_id";
			}
			else
			{
				if($type=='soil')
				{
					//taxon to filter
					$arraySoilMeasure = array();
					if(key_exists("soil_measure",$arrayFilter))
					{
						$arraySoilMeasure = $arrayFilter["soil_measure"];
					}
					//years to filter
					$arrayYear = array();
					if(key_exists("year",$arrayFilter))
					{
						$arrayYear = $arrayFilter["year"];
					}
					//internal id
					$columnPart .= "sampling_id, sample_id, soil.geographical_entity_id AS parcel_id ";
					$columnPart .= ",experience_mod.select_sample_year(soil.sample_id,date_mod.select_date_type('sampling_date_start')) AS sample_year ";
					////table to join
					$joinPart .= "experience_mod.sample AS soil ";
					$joinPart .= "JOIN experience_mod.sample_measure USING(sample_id) ";
					////////where filter
					////needed
					$wherePart .= "soil.sampling_type_id=experience_mod.select_type_sample('soils') ";
					//measure
					if(count($arraySoilMeasure)>0)
					{
						$wherePart .= " AND experiment_measure_id IN(";
						$lastMeasure = array_pop($arraySoilMeasure);
						if(count($arraySoilMeasure)>0)
						{
							foreach($arraySoilMeasure as $measure)
							{
								$wherePart .= "experience_mod.select_type_mesure_exp('$measure',experience_mod.select_type_mesure('soil_measure')), ";
							}
						}
						$wherePart .= "experience_mod.select_type_mesure_exp('$lastMeasure',experience_mod.select_type_mesure('soil_measure'))) ";
					}
					////facultative
					//year bounds
					if(count($arrayYear)>0)
					{
						$yearMin = $arrayYear["min"];
						$yearMax = $arrayYear["max"];
						$wherePart .= "AND experience_mod.select_sample_year(soil.sample_id,date_mod.select_date_type('sampling_date_start')) BETWEEN $yearMin AND $yearMax ";
					}
					////order part
					$orderPart .= "sample_year, soil.geographical_entity_id, soil.sampling_id, soil.sample_id";
				}
				else
				{
					throw new Exception("Wrong type $type of sample wanted");
				}
			}
			$wherePart .= "	AND (copyright_mod.select_copyright(sampling_id)=0) ";
			$query = "$columnPart $joinPart $wherePart $orderPart;";
			$arrayQuery = $this->get($query);
			if(count($arrayQuery)>0)
			{
				$arraySample = $arrayQuery;
			}
			else
			{
				if($type=='fauna')
				{
					//throw new Exception("No sample found database.");
				}
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arraySample);
	}

	/**
	 * Select private message from database
	 * @param array $arraySample Filter in where
	 * @param array $type Type of sample
	 * @throws Exception
	 */
	function selectSamplePrivateMessage($type,$arrayFilter)
	{
		$arrayMessage = NULL;
		try
		{
			////variables
			//query construction
			$query = "";
			if($type=='fauna')
			{
				if(count($arrayFilter)>0)
				{
					$query = "SELECT '(fauna) '||site_mod.select_geo_entity_name(geo_parent.geographical_entity_id)||', '".
						"||site_mod.select_geo_entity_name(ss.geographical_entity_id)||' ('".
						"||experience_mod.select_sample_year(sample_id,date_mod.select_date_type('sampling_date_start'))||'). Source: '".
						"||(reference_mod.select_publication(source_id)).author||' ('".
						"||(reference_mod.select_publication(source_id)).edition_date||') '".
						"||(reference_mod.select_publication(source_id)).title||' '".
						"||(reference_mod.select_publication(source_id)).edition||'. Please contact: <b>'".
						"||owner||'</b>. Release date: '".
						"||embargo_end||'.'".
					"FROM experience_mod.sampling s".
						"LEFT JOIN experience_mod.sample ss USING(sampling_id)".
						"LEFT JOIN site_mod.geographical_entity_inclusion geo_parent ON(ss.geographical_entity_id=geo_parent.geographical_entity_son_id)".
						"LEFT JOIN reference_mod.reference ON(sampling_id=global_identifier_id) ".
						"LEFT JOIN reference_mod.source_reference USING(source_reference_id) ".
						"LEFT JOIN copyright_mod.copyright c ON(sampling_id=c.global_identifier_id) ".
						"LEFT JOIN copyright_mod.copyright_info ci ON(sampling_id=ci.global_identifier_id) ".
					"WHERE accessibility=FALSE".
						"AND sampling_type_id=experience_mod.select_type_sample('fauna') ";
						"AND sample_id IN( ";
					foreach($arrayFilter as $sampleIdTmp=>$val)
					{
						$query .= "$sampleIdTmp, ";
					}
					$query = substr($query, 0, -1).");";
				}
				else
				{
					throw new Exception("No sample private");
				}
			}
			else
			{
				if($type=='soil')
				{
					if(count($arrayFilter)>0)
					{
						$query = "SELECT '(soil) '||site_mod.select_geo_entity_name(geo_parent.geographical_entity_id)||', '".
							"||site_mod.select_geo_entity_name(ss.geographical_entity_id)||' ('".
							"||experience_mod.select_sample_year(sample_id,date_mod.select_date_type('sampling_date_start'))||'). Source: '".
							"||(reference_mod.select_publication(source_id)).author||' ('".
							"||(reference_mod.select_publication(source_id)).edition_date||') '".
							"||(reference_mod.select_publication(source_id)).title||' '".
							"||(reference_mod.select_publication(source_id)).edition||'. Please contact: <b>'".
							"||owner||'</b>. Release date: '".
							"||embargo_end||'.'".
						"FROM experience_mod.sampling s".
							"LEFT JOIN experience_mod.sample ss USING(sampling_id)".
							"LEFT JOIN site_mod.geographical_entity_inclusion geo_parent ON(ss.geographical_entity_id=geo_parent.geographical_entity_son_id)".
							"LEFT JOIN reference_mod.reference ON(sampling_id=global_identifier_id) ".
							"LEFT JOIN reference_mod.source_reference USING(source_reference_id) ".
							"LEFT JOIN copyright_mod.copyright c ON(sampling_id=c.global_identifier_id) ".
							"LEFT JOIN copyright_mod.copyright_info ci ON(sampling_id=ci.global_identifier_id) ".
						"WHERE accessibility=FALSE".
							"AND sampling_type_id=experience_mod.select_type_sample('soil') ";
							"AND sample_id IN( ";
						foreach($arrayFilter as $sampleIdTmp=>$val)
						{
							$query .= "$sampleIdTmp, ";
						}
						$query = substr($query, 0, -1).");";
					}
					else
					{
						throw new Exception("No sample private");
					}
				}
				else
				{
					throw new Exception("Wrong type $type of sample wanted");
				}
			}
			$arrayQuery = $this->get($query);
			if(count($arrayQuery)>0)
			{
				$arrayMessage = $arrayQuery;
			}
			else
			{
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayMessage);
	}
	
	/**
	 * Select sample from database
	 * @param array $arrayFilter Filter in where
	 * @param array $arrayPrinting Column to print
	 * @throws Exception
	 */
	function selectSample($type,$arrayFilter,$isAdmin=0,$owner=NULL)
	{
		$arraySample = NULL;
		try
		{
			////variables
			//query construction
			$query = "";
			$columnPart = "SELECT ";
			$joinPart = "FROM ";
			$wherePart = "WHERE ";
			$orderPart = "ORDER BY ";
			if($type=='fauna')
			{
				//taxon to filter
				$arrayTaxo = $arrayFilter["taxo"];
				if(count($arrayTaxo)==0)
				{
					throw new Exception("No taxon chosen.");
				}
				//type observation to filter
				$arrayTypeObs = $arrayFilter["type_obs"];
				if(count($arrayTypeObs)==0)
				{
					throw new Exception("No type observation chosen.");
				}
				//years to filter
				$arrayYear = array();
				if(key_exists("year",$arrayFilter))
				{
					$arrayYear = $arrayFilter["year"];
				}
				////programmation side
				//internal id
				$columnPart .= "sampling_id, sample_id, observation_id, fauna.geographical_entity_id AS parcel_id, taxon_id, observation_value ";
				$columnPart .= ", experience_mod.select_sample_year(fauna.sample_id,date_mod.select_date_type('sampling_date_start')) AS sample_year ";
				////table to join
				$joinPart .= "experience_mod.sample AS fauna ".
						"JOIN experience_mod.sampling AS fauning USING(sampling_id) ".
						"JOIN experience_mod.observation USING(sample_id) ";
				////////where filter
				////needed
				$wherePart .= "fauna.sampling_type_id=experience_mod.select_type_sample('fauna') ";
				//taxon
				$wherePart .= " AND taxon_id IN(";
				$lastTaxo = array_pop($arrayTaxo);
				if(count($arrayTaxo)>0)
				{
					foreach($arrayTaxo as $taxo)
					{
						$wherePart .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$taxo') UNION ";
					}
				}
				$wherePart .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$lastTaxo')) AND ";
				//observation type
				$wherePart .= "observation_type_id IN(";
				$lastTypeObs = array_pop($arrayTypeObs);
				if(count($arrayTypeObs)>0)
				{
					foreach($arrayTypeObs as $typeObs)
					{
						$wherePart .= "experience_mod.select_type_observation('$typeObs'), ";
					}
				}
				$wherePart .= "experience_mod.select_type_observation('$lastTypeObs')) ";
				////facultative
				//year bounds
				if(count($arrayYear)>0)
				{
					$yearMin = $arrayYear["min"];
					$yearMax = $arrayYear["max"];
					$wherePart .= "AND experience_mod.select_sample_year(fauna.sample_id,date_mod.select_date_type('sampling_date_start')) BETWEEN $yearMin AND $yearMax ";
				}
				////order part
				$orderPart .= "sample_year, fauna.geographical_entity_id, fauna.sampling_id, fauna.sample_id";
			}
			else
			{
				if($type=='soil')
				{
					//taxon to filter
					$arraySoilMeasure = array();
					if(key_exists("soil_measure",$arrayFilter))
					{
						$arraySoilMeasure = $arrayFilter["soil_measure"];
					}
					//years to filter
					$arrayYear = array();
					if(key_exists("year",$arrayFilter))
					{
						$arrayYear = $arrayFilter["year"];
					}
					//internal id
					$columnPart .= "sampling_id, sample_id, soil.geographical_entity_id AS parcel_id ";
					$columnPart .= ",experience_mod.select_sample_year(soil.sample_id,date_mod.select_date_type('sampling_date_start')) AS sample_year ";
					////table to join
					$joinPart .= "experience_mod.sample AS soil ";
					$joinPart .= "JOIN experience_mod.sample_measure USING(sample_id) ";
					////////where filter
					////needed
					$wherePart .= "soil.sampling_type_id=experience_mod.select_type_sample('soils') ";
					//measure
					if(count($arraySoilMeasure)>0)
					{
						$wherePart .= " AND experiment_measure_id IN(";
						$lastMeasure = array_pop($arraySoilMeasure);
						if(count($arraySoilMeasure)>0)
						{
							foreach($arraySoilMeasure as $measure)
							{
								$wherePart .= "experience_mod.select_type_mesure_exp('$measure',experience_mod.select_type_mesure('soil_measure')), ";
							}
						}
						$wherePart .= "experience_mod.select_type_mesure_exp('$lastMeasure',experience_mod.select_type_mesure('soil_measure'))) ";
					}
					////facultative
					//year bounds
					if(count($arrayYear)>0)
					{
						$yearMin = $arrayYear["min"];
						$yearMax = $arrayYear["max"];
						$wherePart .= "AND experience_mod.select_sample_year(soil.sample_id,date_mod.select_date_type('sampling_date_start')) BETWEEN $yearMin AND $yearMax ";
					}
					////order part
					$orderPart .= "sample_year, soil.geographical_entity_id, soil.sampling_id, soil.sample_id";
				}
				else
				{
					throw new Exception("Wrong type $type of sample wanted");
				}
			}
			if($isAdmin==0)
			{
				$wherePart .= "	AND (copyright_mod.select_copyright(sampling_id)=1 OR (copyright_mod.select_copyright(sampling_id)=0 AND copyright_mod.select_copyright_owner(sampling_id) ILIKE '$owner')) ";
			}
			$query = "$columnPart $joinPart $wherePart $orderPart;";
			$arrayQuery = $this->get($query);
			if(count($arrayQuery)>0)
			{
				$arraySample = $arrayQuery;
			}
			else
			{
				if($type=='fauna')
				{
					throw new Exception("No sample found database.");
				}
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arraySample);
	}
	
	/**
	 * Select quantitative soil measure by parcel, year and sample layer for a geo and a year
	 * @param integer $idGeo
	 * @param integer $year
	 * @param array $arrayMeasure
	 */
	function selectAllSoilMeasureByYear($idGeo,$year,$type='mean',$arraySoilMeasure=NULL)
	{
		$arrayMes = NULL;
		try
		{
			if(is_integer($idGeo))
			{
				if(is_integer($year))
				{
					$query = "SELECT experiment_measure_code, ";
					$query .= "my_string_agg(CAST(sample_id AS VARCHAR)) AS array_sample_id, my_string_agg(CAST(sampling_id AS VARCHAR)) AS array_sampling_id, ";
					if($type=='mean')
					{
						$query .= "AVG(CAST(sample_measure_value AS float)) AS agg, ";
					}
					else
					{
						if($type=='median')
						{
							$query .= "MEDIAN(CAST(sample_measure_value AS numeric)) AS agg, ";
						}
						else
						{
							throw new Exception("Wrong type of aggregation $type given.");
						}
					}
					$query .= "(experience_mod.select_sample_measure(sample_id,experience_mod.select_type_mesure_exp('sample_layer',experience_mod.select_type_mesure('sample')))).measure_value AS sample_layer ".
						"FROM experience_mod.sample ".
							"JOIN experience_mod.sample_measure USING(sample_id) ".
							"JOIN experience_mod.experiment_measure USING(experiment_measure_id) ".
						"WHERE experiment_measure_sql_type IN('int4','float8') ".
							"AND experience_mod.select_sample_year(sample_id,date_mod.select_date_type('sampling_date_start'))=$year ".
							"AND geographical_entity_id=$idGeo ";
					if(is_array($arraySoilMeasure))
					{
						if(count($arraySoilMeasure)>0)
						{
							$query .= "AND experiment_measure_id IN( ";
							$lastMeasure = array_pop($arraySoilMeasure);
							if(count($arraySoilMeasure)>0)
							{
								foreach($arraySoilMeasure as $measure)
								{
									$query .= "experience_mod.select_type_mesure_exp('$measure',experience_mod.select_type_mesure('soil_measure')), ";
								}
							}
							$query .= "experience_mod.select_type_mesure_exp('$lastMeasure',experience_mod.select_type_mesure('soil_measure'))) ";
						}
					}
					$query .= "GROUP BY experiment_measure_code, ".
						"(experience_mod.select_sample_measure(sample_id,experience_mod.select_type_mesure_exp('sample_layer', ".
						"experience_mod.select_type_mesure('sample')))).measure_value;";
					$arrayQuery = $this->get($query);
					if(!is_null($arrayQuery)&&count($arrayQuery)>0)
					{
						$arrayMes = $arrayQuery;
					}
				}
				else
				{
					throw new Exception("Wrong parameter year $year given.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter idGeo $idGeo given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayMes);
	}
	
	/**
	 * Select qualitative soil measure by parcel, year and sample layer for a geo and a year
	 * @param integer $idGeo
	 * @param integer $year
	 * @param array $arrayMeasure
	 */
	function selectAllSoilQualitativeMeasureByYear($idGeo,$year,$arraySoilMeasure=NULL)
	{
		$arrayMes = NULL;
		try
		{
			if(is_integer($idGeo))
			{
				if(is_integer($year))
				{
					$query = "SELECT experiment_measure_code,string_agg(sample_measure_value,':') AS agg, ";
					$query .= "my_string_agg(CAST(sample_id AS VARCHAR)) AS array_sample_id, my_string_agg(CAST(sampling_id AS VARCHAR)) AS array_sampling_id, ";
					$query .= "(experience_mod.select_sample_measure(sample_id,experience_mod.select_type_mesure_exp('sample_layer',experience_mod.select_type_mesure('sample')))).measure_value AS sample_layer ".
							"FROM experience_mod.sample ".
							"JOIN experience_mod.sample_measure USING(sample_id) ".
							"JOIN experience_mod.experiment_measure USING(experiment_measure_id) ".
							"WHERE experiment_measure_sql_type IN('varchar') ".
							"AND experience_mod.select_sample_year(sample_id,date_mod.select_date_type('sampling_date_start'))=$year ".
							"AND geographical_entity_id=$idGeo ";
					if(is_array($arraySoilMeasure))
					{
						if(count($arraySoilMeasure)>0)
						{
							$query .= "AND experiment_measure_id IN( ";
							$lastMeasure = array_pop($arraySoilMeasure);
							if(count($arraySoilMeasure)>0)
							{
								foreach($arraySoilMeasure as $measure)
								{
									$query .= "experience_mod.select_type_mesure_exp('$measure',experience_mod.select_type_mesure('soil_measure')), ";
								}
							}
							$query .= "experience_mod.select_type_mesure_exp('$lastMeasure',experience_mod.select_type_mesure('soil_measure'))) ";
						}
					}
					$query .= "GROUP BY experiment_measure_code, ".
							"(experience_mod.select_sample_measure(sample_id,experience_mod.select_type_mesure_exp('sample_layer', ".
							"experience_mod.select_type_mesure('sample')))).measure_value;";
					$arrayQuery = $this->get($query);
					if(!is_null($arrayQuery)&&count($arrayQuery)>0)
					{
						$arrayMes = $arrayQuery;
					}
				}
				else
				{
					throw new Exception("Wrong parameter year $year given.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter idGeo $idGeo given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayMes);
	}
	
	function selectSampleDate($arraySampleId,$typeDate)
	{
		$valueMes = NULL;
		try
		{
			if(is_array($arraySampleId)&&count($arraySampleId)>0)
			{
				if(is_string($typeDate))
				{
					$query = "SELECT my_string_agg(experience_mod.select_sample_date(sample_id,date_mod.select_date_type('$typeDate'))) AS agg ".
							"FROM experience_mod.sample ".
							"WHERE sample_id IN( ";
					end($arraySampleId);
					$lastId = key($arraySampleId);
					array_pop($arraySampleId);
					if(count($arraySampleId)>0)
					{
						foreach($arraySampleId as $id=>$val)
						{
							$query .= "$id, ";
						}
					}
					$query .= "$lastId);";
					$arrayQuery = $this->get($query);
					if(!is_null($arrayQuery[0]))
					{
						$valueMes = $arrayQuery[0]["agg"];
					}
				}
				else
				{
					throw new Exception("Wrong parameter typeDate $typeDate given.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter arraySampleId given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($valueMes);
	}
	
	function selectSampleMeasure($arraySampleId,$measure,$typeMeasure)
	{
		$valueMes = NULL;
		try
		{
			if(is_array($arraySampleId)&&count($arraySampleId)>0)
			{
				if(is_string($measure))
				{
					if(is_string($measure))
					{
						$query = "SELECT my_string_agg((experience_mod.select_sample_measure(sample_id,experience_mod.select_type_mesure_exp('$measure',experience_mod.select_type_mesure('$typeMeasure')))).measure_value) AS agg ".
								"FROM experience_mod.sample_measure ".
								"WHERE sample_id IN( ";
						end($arraySampleId);
						$lastId = key($arraySampleId);
						array_pop($arraySampleId);
						if(count($arraySampleId)>0)
						{
							foreach($arraySampleId as $id=>$val)
							{
								$query .= "$id, ";
							}
						}
						$query .= "$lastId);";
						$arrayQuery = $this->get($query);
						if(!is_null($arrayQuery[0]))
						{
							$valueMes = $arrayQuery[0]["agg"];
						}
					}
					else
					{
						throw new Exception("Wrong parameter typeMeasure $typeMeasure given.");
					}
				}
				else
				{
					throw new Exception("Wrong parameter measure $measure given.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter arraySampleId given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($valueMes);
	}

	function selectSamplingMeasure($arraySampleId,$measure,$typeMeasure)
	{
		$valueMes = NULL;
		try
		{
			if(is_array($arraySampleId)&&count($arraySampleId)>0)
			{
				if(is_string($measure))
				{
					if(is_string($measure))
					{
						$query = "SELECT my_string_agg(experience_mod.select_sampling_measure(sampling_id,experience_mod.select_type_mesure_exp('$measure',experience_mod.select_type_mesure('$typeMeasure')))) AS agg ".
								"FROM experience_mod.sample ".
									"JOIN experience_mod.sampling_measure USING(sampling_id) ".
								"WHERE sample_id IN( ";
						end($arraySampleId);
						$lastId = key($arraySampleId);
						array_pop($arraySampleId);
						if(count($arraySampleId)>0)
						{
							foreach($arraySampleId as $id=>$val)
							{
								$query .= "$id, ";
							}
						}
						$query .= "$lastId);";
						$arrayQuery = $this->get($query);
						if(!is_null($arrayQuery[0]))
						{
							$valueMes = $arrayQuery[0]["agg"];
						}
					}
					else
					{
						throw new Exception("Wrong parameter typeMeasure $typeMeasure given.");
					}
				}
				else
				{
					throw new Exception("Wrong parameter measure $measure given.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter arraySampleId given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($valueMes);
	}
	

	function selectSampleStrategy($arraySampleId)
	{
		$valueMes = NULL;
		try
		{
			if(is_array($arraySampleId)&&count($arraySampleId)>0)
			{
				$query = "SELECT my_string_agg(experience_mod.select_sample_strategy(sample_id)) AS agg ".
						"FROM experience_mod.sample ".
						"WHERE sample_id IN( ";
				end($arraySampleId);
				$lastId = key($arraySampleId);
				array_pop($arraySampleId);
				if(count($arraySampleId)>0)
				{
					foreach($arraySampleId as $id=>$val)
					{
						$query .= "$id, ";
					}
				}
				$query .= "$lastId);";
				$arrayQuery = $this->get($query);
				if(!is_null($arrayQuery[0]))
				{
					$valueMes = $arrayQuery[0]["agg"];
				}
			}
			else
			{
				throw new Exception("Wrong parameter arraySampleId given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($valueMes);
	}
	
	function selectSampleProtocol($arraySampleId)
	{
		$valueMes = NULL;
		try
		{
			if(is_array($arraySampleId)&&count($arraySampleId)>0)
			{
				$query = "SELECT my_string_agg(experience_mod.select_sample_protocol(sample_id)) AS agg ".
						"FROM experience_mod.sample ".
						"WHERE sample_id IN( ";
				end($arraySampleId);
				$lastId = key($arraySampleId);
				array_pop($arraySampleId);
				if(count($arraySampleId)>0)
				{
					foreach($arraySampleId as $id=>$val)
					{
						$query .= "$id, ";
					}
				}
				$query .= "$lastId);";
				$arrayQuery = $this->get($query);
				if(!is_null($arrayQuery[0]))
				{
					$valueMes = $arrayQuery[0]["agg"];
				}
			}
			else
			{
				throw new Exception("Wrong parameter arraySampleId given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($valueMes);
	}
	
	
	/**
	 * Select aggregate taxon observation of a parcel by year, type, filter by taxon id
	 * @param integer $idGeo
	 * @param integer $year
	 * @param string $typeObs
	 * @param string $type
	 * @param array $arrayTaxon
	 * @throws Exception
	 */
	function selectAllObservationByYear($idGeo,$year,$typeObs,$type='mean',$arrayTaxon=NULL)
	{
		$arrayObs = NULL;
		try
		{
			if(is_integer($idGeo))
			{
				if(is_integer($year))
				{
					$query = "SELECT taxonomy_mod.select_taxo_name(taxon_id) AS name_taxon, ";
					$query .= "my_string_agg(CAST(sample_id AS VARCHAR)) AS array_sample_id, my_string_agg(CAST(sampling_id AS VARCHAR)) AS array_sampling_id, ";
					if($type=='mean')
					{
						$query .= "AVG(observation_value) AS agg, ";
					}
					else
					{
						if($type=='median')
						{
							$query .= "median(observation_value) AS agg, ";
						}
						else
						{
							throw new Exception("Wrong type of aggregation $type given.");
						}
					}
					$query .= "experience_mod.select_protocol_name(sampling_protocol_id) AS name_protocol ".
						"FROM experience_mod.sample ".
							"JOIN experience_mod.observation USING(sample_id) ".
							"JOIN experience_mod.sampling USING(sampling_id) ".
						"WHERE experience_mod.select_sample_year(sample_id,date_mod.select_date_type('sampling_date_start'))=$year ".
							"AND geographical_entity_id=$idGeo ".
							"AND experience_mod.select_type_observation_name(observation_type_id)='$typeObs' ";
					if(is_array($arrayTaxon))
					{
						if(count($arrayTaxon)>0)
						{
							$query .= "AND taxon_id IN( ";
							$lastIdTaxon = array_pop($arrayTaxon);
							if(count($arrayTaxon)>0)
							{
								foreach($arrayTaxon as $idTaxon)
								{
									$query .= "$idTaxon, ";
								}
							}
							$query .= "$lastIdTaxon) ";
						}
					}
					$query .= "GROUP BY taxon_id, sampling_protocol_id;";
					$arrayQuery = $this->get($query);
					if(!is_null($arrayQuery)&&count($arrayQuery)>0)
					{
						$arrayObs = $arrayQuery;
					}
				}
				else
				{
					throw new Exception("Wrong parameter year $year given.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter idGeo $idGeo given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayObs);
	}
	
	/**
	 * Select aggregate trait of a parcel by year, type, taxon filter by taxon id for one trait name and its synonyms
	 * @param integer $idGeo
	 * @param integer $year
	 * @param string $type
	 * @param array $arrayTaxon
	 * @param array $arrayTrait
	 * @throws Exception
	 * @return unknown
	 */
	function selectTraitByYearSyn($idGeo,$year,$type='mean',$arrayTaxon=NULL,$arrayTrait=NULL)
	{
		$arrayTraitReturn = NULL;
		try
		{
			if(is_integer($idGeo))
			{
				if(is_integer($year))
				{
					$query = "SELECT taxonomy_mod.select_taxo_name(taxon_id) AS name_taxon, ";
					if($type=='mean')
					{
						$query .= "AVG(CAST(trait_value AS numeric)) AS agg ";
					}
					else
					{
						if($type=='median')
						{
							$query .= "median(CAST(trait_value AS numeric)) AS agg ";
						}
						else
						{
							throw new Exception("Wrong type of aggregation $type given.");
						}
					}
					$query .= "FROM experience_mod.sample ".
							"JOIN trait_mod.trait_value USING(sample_id) ".
							"JOIN trait_mod.trait USING(trait_id) ".
							"WHERE experience_mod.select_sample_year(sample_id,date_mod.select_date_type('sampling_date_start'))=$year ".
							"AND geographical_entity_id=$idGeo ".
							"AND trait_sql_type IN('int4','float8') ";
					if(is_array($arrayTaxon))
					{
						if(count($arrayTaxon)>0)
						{
							$query .= "AND taxon_id IN( ";
							$lastIdTaxon = array_pop($arrayTaxon);
							if(count($arrayTaxon)>0)
							{
								foreach($arrayTaxon as $idTaxon)
								{
									$query .= "$idTaxon, ";
								}
							}
							$query .= "$lastIdTaxon) ";
						}
					}
					if(is_array($arrayTrait))
					{
						if(count($arrayTrait)>0)
						{
							$query .= "AND trait_name IN( ";
							$lastTrait = array_pop($arrayTrait);
							if(count($arrayTrait)>0)
							{
								foreach($arrayTrait as $trait)
								{
									$query .= "'$trait', ";
								}
							}
							$query .= "'$lastTrait') ";
						}
					}
					$query .= "GROUP BY taxon_id;";
					$arrayQuery = $this->get($query);
					if(!is_null($arrayQuery)&&count($arrayQuery)>0)
					{
						$arrayTraitReturn = $arrayQuery;
					}
				}
				else
				{
					throw new Exception("Wrong parameter year $year given.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter idGeo $idGeo given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayTraitReturn);
	}
	
	/**
	 * Select aggregate trait of a parcel by year, type, taxon filter by taxon id and trait name
	 * @param integer $idGeo
	 * @param integer $year
	 * @param string $type
	 * @param array $arrayTaxon
	 * @param array $arrayTrait
	 * @throws Exception
	 * @return unknown
	 */
	function selectAllTraitByYear($idGeo,$year,$type='mean',$arrayTaxon=NULL,$arrayTrait=NULL)
	{
		$arrayTraitReturn = NULL;
		try
		{
			if(is_integer($idGeo))
			{
				if(is_integer($year))
				{
					$query = "SELECT taxonomy_mod.select_taxo_name(taxon_id) AS name_taxon, trait_name AS name_trait, ";
					if($type=='mean')
					{
						$query .= "AVG(CAST(trait_value AS numeric)) AS agg ";
					}
					else
					{
						if($type=='median')
						{
							$query .= "median(CAST(trait_value AS numeric)) AS agg ";
						}
						else
						{
							throw new Exception("Wrong type of aggregation $type given.");
						}
					}
					$query .= "FROM experience_mod.sample ".
							"JOIN trait_mod.trait_value USING(sample_id) ".
							"JOIN trait_mod.trait USING(trait_id) ".
						"WHERE experience_mod.select_sample_year(sample_id,date_mod.select_date_type('sampling_date_start'))=$year ".
							"AND geographical_entity_id=$idGeo ".
							"AND trait_sql_type IN('int4','float8') ";
					if(is_array($arrayTaxon))
					{
						if(count($arrayTaxon)>0)
						{
							$query .= "AND taxon_id IN( ";
							$lastIdTaxon = array_pop($arrayTaxon);
							if(count($arrayTaxon)>0)
							{
								foreach($arrayTaxon as $idTaxon)
								{
									$query .= "$idTaxon, ";
								}
							}
							$query .= "$lastIdTaxon) ";
						}
					}
					if(is_array($arrayTrait))
					{
						if(count($arrayTrait)>0)
						{
							$query .= "AND trait_name IN( ";
							$lastTrait = array_pop($arrayTrait);
							if(count($arrayTrait)>0)
							{
								foreach($arrayTrait as $trait)
								{
									$query .= "'$trait', ";
								}
							}
							$query .= "'$lastTrait') ";
						}
					}
					$query .= "GROUP BY taxon_id, trait_name;";
					$arrayQuery = $this->get($query);
					if(!is_null($arrayQuery)&&count($arrayQuery)>0)
					{
						$arrayTraitReturn = $arrayQuery;
					}
				}
				else
				{
					throw new Exception("Wrong parameter year $year given.");
				}
			}
			else
			{
				throw new Exception("Wrong parameter idGeo $idGeo given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayTraitReturn);
	}	
	
	/**
	 * Select sample from database
	 * @param array $arrayFilter Filter in where
	 * @param array $arrayPrinting Column to print
	 * @throws Exception
	 */
	function selectSampleOld($arrayFilter,$arrayPrinting)
	{
		$arraySample = NULL;
		try
		{
			////variables
			//taxon to filter
			$arrayTaxo = $arrayFilter["taxo"];
			if(count($arrayTaxo)==0)
			{
				throw new Exception("No taxon chosen.");
			}
			//type observation to filter
			$arrayTypeObs = $arrayFilter["type_obs"];
			if(count($arrayTypeObs)==0)
			{
				throw new Exception("No type observation chosen.");
			}
			//plot name to filter
			$arrayPlot = array();
			if(key_exists("plot",$arrayFilter))
			{
				$arrayPlot = $arrayFilter["plot"];
			}
			//parcel name to filter
			$arrayParcel = array();
			if(key_exists("parcel",$arrayFilter))
			{
				$arrayParcel = $arrayFilter["parcel"];
			}
			//years to filter
			$arrayYear = array();
			if(key_exists("year",$arrayFilter))
			{
				$arrayYear = $arrayFilter["year"];
			}
			////query construction
			$query = "";
			////programmation side
			//internal id
			$columnPart = "SELECT sampling_id, sample_id, observation_id, plot.geographical_entity_id AS plot_id, parcel.geographical_entity_id AS parcel_id, taxonomy_id ";
			////column to print
			//sample fauna
			$columnPart .= ", reference_mod.select_ref(fauna.sample_id) AS fauna_sample_name, experience_mod.select_sample_date(fauna.sample_id,date_mod.select_date_type('sampling_date_start')) AS fauna_sampling_date_start ";
			//observations
			//measures
			$columnPart .= ", (experience_mod.select_sample_measure(fauna.sample_id,experience_mod.select_type_mesure_exp('sample_layer', experience_mod.select_type_mesure('sample')))).measure_value AS sample_layer ";
			$columnPart .= ", (experience_mod.select_sample_measure(fauna.sample_id,experience_mod.select_type_mesure_exp('sample_layer', experience_mod.select_type_mesure('sample')))).point_number AS point_number ";
			$columnPart .= ", (experience_mod.select_sample_measure(fauna.sample_id,experience_mod.select_type_mesure_exp('sample_replicate_nb', experience_mod.select_type_mesure('sample_replicate')))).measure_value AS sample_replicate_nb ";
			//sampling
			$columnPart .= ", experience_mod.select_protocol_name(fauning.sampling_protocol_id), experience_mod.select_strategy_name(fauning.sampling_strategy_id) ";
			$columnPart .= ", experience_mod.select_sampling_measure(fauning.sampling_id,experience_mod.select_type_mesure_exp('chemical_product', experience_mod.select_type_mesure('sampling_protocol'))) AS chemical_product ";
			$columnPart .= ", experience_mod.select_sampling_measure(fauning.sampling_id,experience_mod.select_type_mesure_exp('soil_extraction', experience_mod.select_type_mesure('sampling_protocol'))) AS soil_extraction ";
			//geography
			$columnPart .= ",plot.geographical_entity_name AS name_plot, parcel.geographical_entity_name AS name_parcel, loc.x AS longitude, loc.y AS latitude ";
				
			////table to join
			$joinPart = "FROM experience_mod.sample AS fauna ".
					"JOIN experience_mod.sampling AS fauning USING(sampling_id) ".
					"JOIN experience_mod.observation USING(sample_id) ".
					"JOIN site_mod.geographical_entity_location loc USING(geographical_entity_id) ".
					"JOIN site_mod.geographical_entity parcel USING(geographical_entity_id) ".
					"JOIN site_mod.geographical_entity_inclusion geo_parent ON(loc.geographical_entity_id=geo_parent.geographical_entity_son_id) ".
					"JOIN site_mod.geographical_entity plot ON(plot.geographical_entity_id=geo_parent.geographical_entity_id) "
					;
					////////where filter
					////needed
					$wherePart = "WHERE fauna.sampling_type_id=experience_mod.select_type_sample('fauna') ";
					//taxon
					$wherePart .= " AND taxon_id IN(";
					$lastTaxo = array_pop($arrayTaxo);
					if(count($arrayTaxo)>0)
					{
						foreach($arrayTaxo as $taxo)
						{
							$wherePart .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$taxo') UNION ";
						}
					}
					$wherePart .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$lastTaxo')) AND ";
					//observation type
					$wherePart .= "observation_type_id IN(";
					$lastTypeObs = array_pop($arrayTypeObs);
					if(count($arrayTypeObs)>0)
					{
						foreach($arrayTypeObs as $typeObs)
						{
							$wherePart .= "experience_mod.select_type_observation('$typeObs'), ";
						}
					}
					$wherePart .= "experience_mod.select_type_observation('$lastTypeObs')) ";
					////facultative
					//parcel name
					if(count($arrayParcel)>0)
					{
						$typeGeo = 'parcel';
						$wherePart .= "AND fauna.geographical_entity_id IN(";
						$lastNameParcel = array_pop($arrayParcel);
						if(count($arrayParcel)>0)
						{
							foreach($arrayParcel as $nameParcel)
							{
								$wherePart .= "site_mod.select_geo_entity(site_mod.select_type_geo('$typeGeo'),'$nameParcel'),";
							}
						}
						$wherePart .= "site_mod.select_geo_entity(site_mod.select_type_geo('$typeGeo'),'$lastNameParcel')) ";
					}
					//plot name
					if(count($arrayPlot)>0)
					{
						$typeGeo = 'plot';
						$wherePart .= "AND geo_parent.geographical_entity_id IN(";
						$lastNamePlot = array_pop($arrayPlot);
						if(count($arrayPlot)>0)
						{
							foreach($arrayPlot as $namePlot)
							{
								$wherePart .= "site_mod.select_geo_entity(site_mod.select_type_geo('$typeGeo'),'$namePlot'),";
							}
						}
						$wherePart .= "site_mod.select_geo_entity(site_mod.select_type_geo('$typeGeo'),'$lastNamePlot')) ";
					}
					//year bounds
					if(count($arrayYear)>0)
					{
						$yearMin = $arrayYear["min"];
						$yearMax = $arrayYear["max"];
						$wherePart .= "AND  experience_mod.select_sample_year(fauna.sample_id,date_mod.select_date_type('sampling_date_start'))>=$yearMin ";
						$wherePart .= "AND  experience_mod.select_sample_year(fauna.sample_id,date_mod.select_date_type('sampling_date_start'))<=$yearMax ";
					}
					////order part
					$orderPart = "ORDER BY name_plot, name_parcel, fauna_sample_name";
					$query = "$columnPart $joinPart $wherePart $orderPart;";
					$arrayQuery = $this->get($query);
					if(count($arrayQuery)>0)
					{
						$arraySample = $arrayQuery;
					}
					else
					{
						throw new Exception("No sample found database.");
					}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arraySample);
	}

	
	/**
	 *
	 * Measurement insertion
	 * @param MeasurementObject $myMeasure
	 * @param integer $idObj
	 * @param Can be 'sample', 'sample_replicate','soil_measure','sampling_protocol','sampling_strategy'
	 * @throws Exception
	 * @return Ambigous <NULL, number>
	 */
	function insertMeasurement($myMeasure,$idObj,$typeMeasure)
	{
		$return = NULL;
		try
		{
			if(is_a($myMeasure,"Measurement"))
			{
				if(is_int($idObj))
				{
					$nameMeasure = $myMeasure->getColumnName();
					$idMeasure = $this->selectTypeMeasureExpId($nameMeasure, $typeMeasure);
					$typeSqlValue = $this->selectMeasureSqlType($idMeasure);
					$value = $myMeasure->getValue();
					if($this->checkTypeValue($value,$typeSqlValue))
					{
						if(!$this->transactionInCourse)
						{
							$this->dbh->beginTransaction();
						}
						$query = NULL;
						if($typeMeasure=='sampling')
						{
							$query = "SELECT experience_mod.measure_sampling_s_i_u($idObj,".
									"experience_mod.select_type_mesure_exp('$nameMeasure',experience_mod.select_type_mesure('$typeMeasure')),'$value') AS id_mes;";
						}else{
							$nbPoint = $myMeasure->getNumberSample();
							if(is_null($nbPoint))
							{
								$query = "SELECT experience_mod.insert_mesure_sample($idObj,$idMeasure,'$value',0) AS id_mes;";
							}
							else
							{
								$query = "SELECT experience_mod.insert_mesure_sample($idObj,$idMeasure,'$value',$nbPoint) AS id_mes;";
							}
						}
						$arrayQuery = $this->get($query);
						if(!is_null($arrayQuery[0]["id_mes"]))
						{
							$return = intval($arrayQuery[0]["id_mes"]);
						}
						if(!$this->transactionInCourse)
						{
							$this->dbh->commit();
						}
					}
					else
					{
						if($typeSqlValue=='float8')
						{
							throw new Exception("Error: $nameMeasure must be a number. Please correct, re-save and re-insert.");
						}else{
							if($typeSqlValue=='int')
							{
								throw new Exception("Error: $nameMeasure must be a integer. Please correct, re-save and re-insert.");
							}else{
								throw new Exception("Wrong SQL type for measure $nameMeasure value $value.");								
							}
						}
					}
				}
				else
				{
					throw new Exception("Wrong argument idObj $idObj given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument Measure given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}	
	
	/**
	 *
	 * Measure insertion
	 * @param MeasureObject $myMeasure
	 * @param integer $idObj
	 * @throws Exception
	 * @return Ambigous <NULL, number>
	 */
	function insertMeasure($myMeasure,$idObj)
	{
		$return = NULL;
		try
		{
			if(is_a($myMeasure,"MeasureExp"))
			{
				if(is_int($idObj))
				{
					$typeMeasure = $myMeasure->get("type_mesure");
					$nameMeasure = $myMeasure->get("name_mesure");
					$idMeasure = $this->selectTypeMeasureExpId($nameMeasure, $typeMeasure);
					$typeSqlValue = $this->selectMeasureSqlType($idMeasure);
					$value = $myMeasure->get("value");
					if($this->checkTypeValue($value,$typeSqlValue))
					{
						if(!$this->transactionInCourse)
						{
							$this->dbh->beginTransaction();
						}
						$query = NULL;
						if($typeMeasure=='sample'||$typeMeasure=='sample_replicate'||$typeMeasure=='soil_measure')
						{
							//TODO a more beautiful way to manage metacolonne in mesure without nbPoint
							$nbPoint =$myMeasure->get("nb_sample");
							if(is_null($nbPoint))
							{
								$query = "SELECT experience_mod.insert_mesure_sample($idObj,$idMeasure,'$value',0) AS id_mes;";
							}
							else
							{
								$query = "SELECT experience_mod.insert_mesure_sample($idObj,$idMeasure,'$value',$nbPoint) AS id_mes;";
							}
						}
						else
						{
							if($typeMeasure=='sampling_protocol'||$typeMeasure=='sampling_strategy')
							{
								$query = "SELECT experience_mod.measure_sampling_s_i_u($idObj,".
									"experience_mod.select_type_mesure_exp('$nameMeasure',experience_mod.select_type_mesure('$typeMeasure')),'$value') AS id_mes;";
							}
							else
							{
								throw new Exception("Wrong type mesure $typeMeasure");
							}
						}
						$arrayQuery = $this->get($query);
						if(!is_null($arrayQuery[0]["id_mes"]))
						{
							$return = intval($arrayQuery[0]["id_mes"]);
						}
						if(!$this->transactionInCourse)
						{
							$this->dbh->commit();
						}
					}
					else
					{
						throw new Exception("Wrong SQL type for measure $nameMeasure value $value.");
					}
				}
				else
				{
					throw new Exception("Wrong argument idObj $idObj given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument Measure given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}
	
	/**
	 * Check if metacolumn value are correct
	 * @param string $value
	 * @param string $type
	 * @throws Exception
	 * @return boolean
	 */
	function checkTypeValue($value,$type)
	{
		$return = FALSE;
		try
		{
			switch($type)
			{
				case 'varchar':
						$return = TRUE;
					
					break;
				case 'float8':
					if(preg_match("/^\-?[0-9]+\.?[0-9]*$/",trim($value)))
					{
						$return = TRUE;
					}else{
						if(preg_match("/,/",trim($value)))
						{
							throw new Exception("Decimal numbers must have\".\" instead of ,");							
						}

					}
					break;
				case 'int4':
					if(preg_match("/^\-?[0-9]+$/",trim($value)))
					{
						$return = TRUE;
					}
					break;
				case 'interval':
					if(preg_match("/^\[[0-9]+,[0-9]+\](U\[[0-9]+,[0-9]+\])*$/",trim($value)))
					{
						$return = TRUE;
					}
					break;
				default:
					throw new Exception("Type SQL $type not managed.");
				break;
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}
	
	/**
	 * Select all informations on experimental measure from code and type
	 * @param string $nameMes
	 * @param string $typeMes
	 * @throws Exception
	 */
	function selectTypeMeasureExpInfo($nameMes,$typeMes)
	{
		$arrayMeasure = NULL;
		try
		{
				if(is_string($typeMes))
				{
					if(is_string($nameMes))
					{
							$query = "SELECT * FROM experience_mod.select_type_mesure_info('$nameMes', experience_mod.select_type_mesure('$typeMes'));";
							$arrayQuery = $this->get($query);
							if(!is_null($arrayQuery[0]))
							{
								$arrayMeasure = $arrayQuery[0];
							}
							else
							{
								throw new Exception("No measure with code $nameMes and type $typeMes in database.");
							}
					}
					else
					{
						throw new Exception("Wrong type for name measure $nameMes.");
					}
				}
				else
				{
					throw new Exception("Wrong type for type measure $typeMes.");
				}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayMeasure);
	}
	
	/**
	 * Select a measure unique for an experiment
	 * @param string $typeIdExp Type of experiment, sample or sampling
	 * @param integer $idExp Database identifier of the experiment
	 * @param string $nameMes Name of the measure
	 * @param string $typeMes Type of measure
	 * @throws Exception
	 * @return Ambigous <NULL, multitype:>
	 */
	function selectMeasureExp($typeIdExp,$idExp,$nameMes,$typeMes)
	{
		$arrayMeasure = NULL;
		try
		{
			if($typeIdExp=='sample'||$typeIdExp=='sampling')
			{
				if(is_string($typeMes))
				{
					if(is_string($nameMes))
					{
						if(is_int($idExp))
						{
							if($typeIdExp=='sample')
							{
								$query = "SELECT experience_mod.select_sample_measure($idExp,experience_mod.select_type_mesure_exp('$nameMes', experience_mod.select_type_mesure('$typeMes'))) AS measure;";
							}
							else
							{
								$query = "SELECT experience_mod.select_sampling_measure($idExp,experience_mod.select_type_mesure_exp('$nameMes', experience_mod.select_type_mesure('$typeMes'))) AS measure;";
							}
							$arrayQuery = $this->get($query);
							$arrayMeasure = $arrayQuery;
						}
						else
						{
							throw new Exception("Wrong type for id experiment $idExp.");
						}
					}
					else
					{
						throw new Exception("Wrong type for name measure $nameMes.");
					}
				}
				else
				{
					throw new Exception("Wrong type for type measure $typeMes.");
				}
			}
			else
			{
				throw new Exception("Wrong type $typeIdExp of experiment id.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayMeasure);
	}
	
	/**
	 * Select one aggregated measure for samples in an geo entioty
	 * @param integer $idgeo Database identifier of the geo entity
	 * @param string $nameMes Name of the measure
	 * @param string $typeMes Type of measure
	 * @param string $typeTrans Type of transformation
	 * @throws Exception
	 * @return numeric
	 */
	function selectMeasureExpGeo($idGeo,$nameMes,$typeMes,$typeTrans)
	{
		$value = NULL;
		try
		{
			if($typeTrans=='mean'||$typeTrans=='median')
			{
				if(is_string($typeMes))
				{
					if(is_string($nameMes))
					{
						if(is_int($idGeo))
						{
							$idMes = $this->selectTypeMeasureExpId($nameMes, $typeMes);
							$sqlType = $this->selectMeasureSqlType($idMes);
							if($sqlType!=='varchar')
							{
								if($typeTrans=='mean')
								{
									$query = "SELECT AVG(CAST(measure_value AS $sqlType)) AS value FROM experience_mod.select_geo_sample_measure($idGeo,$idMes);";
								}
								if($typeTrans=='median')
								{
									$query = "SELECT CAST(experience_mod.select_geo_sample_measure($idGeo,$idMes) AS $sqlType) AS measure_value;";
								}
							}
							else
							{
								$query = "SELECT measure_value AS value FROM experience_mod.select_geo_sample_measure($idGeo,$idMes);";
							}
							$arrayQuery = $this->get($query);
							if(!is_null($arrayQuery[0]["value"]))
							{
								$value = $arrayQuery[0]["value"];
							}
						}
						else
						{
							throw new Exception("Wrong type for id geo $idGeo.");
						}
					}
					else
					{
						throw new Exception("Wrong type for name measure $nameMes.");
					}
				}
				else
				{
					throw new Exception("Wrong type for type measure $typeMes.");
				}
			}
			else
			{
				throw new Exception("Wrong type $typeTrans of transformation type.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($value);
	}
	
	function selectTypeMeasureExpId($nameMes, $typeMes)
	{
		$idMes = NULL;
		try
		{
			if(is_string($nameMes))
			{
				if(is_string($typeMes))
				{
					$query = "SELECT experience_mod.select_type_mesure_exp('$nameMes',experience_mod.select_type_mesure('$typeMes')) AS id_measure;";
					$arrayQuery = $this->get($query);
					if(!is_null($arrayQuery[0]["id_measure"]))
					{
						$idMes = intval($arrayQuery[0]["id_measure"]);
					}
					else
					{
						throw new Exception("No measure $nameMes of type $typeMes in database.");
					}
				}
				else
				{
					throw new Exception("Wrong type for type measure $typeMes.");
				}
			}
			else
			{
				throw new Exception("Wrong type for name measure $nameMes.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($idMes);
	}
	
	
	/**
	 * Select SQL type for a measure identifier
	 * @param unknown_type $idMes Exp measure database identifier
	 * @throws Exception
	 * @return string
	 */
	function selectMeasureSqlType($idMes)
	{
		$value = NULL;
		try
		{
			if(is_int($idMes))
			{
				$query = "SELECT experience_mod.select_sql_type_measure($idMes) AS sql_type;";
				$arrayQuery = $this->get($query);
				if(!is_null($arrayQuery[0]["sql_type"]))
				{
					$value = $arrayQuery[0]["sql_type"];
				}
				else
				{
					throw new Exception("No sql type for measure $idMes.");
				}
			}
			else
			{
				throw new Exception("Wrong type for id measure $idMes.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($value);
	}
	
	/**
	*
	* Select all observation types in database
	* @throws Exception
	*/
	function selectAllTypeObservation()
	{
		$arrayTypeObservation = NULL;
		try
		{
			$query = "SELECT experience_mod.select_all_type_observation() AS type_observation;";
			$arrayQuery = $this->get($query);
			if(count($arrayQuery)>0)
			{
				$arrayTypeObservation = $arrayQuery;
			}
			else
			{
				throw new Exception("No type observation in database.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayTypeObservation);
	}
	
	/**
	 * Delete sample data
	 * @param string $nameSampling
	 * @param string $nameSample
	 * @throws Exception
	 * @return NULL
	 */
	function deleteSample($idSrc,$nameSample)
	{
		$return = NULL;
		try
		{
			if(is_int($idSrc))
			{
				if(is_string($nameSample))
				{
					//insertion by fonction
					if($this->typeInsert=='functionSQL')
					{
						$query = "SELECT experience_mod.delete_sample(
									reference_mod.select_objet_ref($idSrc,'$nameSample')
								) AS return;";
						$arrayQuery = $this->get($query);
						if(intval($arrayQuery[0]["return"])!==1)
						{
							throw new Exception("Delete sample $nameSample in the source $source failed.");
						}
					}
				}
				else
				{
					throw new Exception("Wrong argument nameSample $nameSample given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument idSrc $idSrc given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}

	/**
	 * Delete sampling data, including sample data
	 * @param string $source
	 * @param string $nameSampling
	 * @throws Exception
	 * @return NULL
	 */
	function deleteSampling($idSrc,$nameSampling)
	{
		$return = NULL;
		try
		{
			if(is_int($idSrc))
			{
				if(is_string($nameSampling))
				{
					//insertion by fonction
					if($this->typeInsert=='functionSQL')
					{
						$query = "SELECT experience_mod.delete_sampling(
										reference_mod.select_objet_ref($idSrc,'$nameSampling')
									) AS return;";
						$arrayQuery = $this->get($query);
						if(intval($arrayQuery[0]["return"])!==1)
						{
							throw new Exception("Delete sampling $nameSampling failed.");
						}
					}
				}
				else
				{
					throw new Exception("Wrong argument nameSampling $nameSampling given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument idSrc $idSrc given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}
	
	/**
	 * Delete all data linked to samplings with metadata fixed and in a parcel
	 * @param integer $idSrc Database identifier of source
	 * @param string $parcelName Name of the parcel
	 * @param string $typeSampling Type of sampling (soils, fauna)
	 * @param string $senderName Name of sender
	 * @param string $creationDate Send date
	 * @throws Exception
	 * @return NULL
	 */
	function deleteSamplingAll($idSrc,$geoEntity,$geoEntityType,$typeSampling,$senderName,$creationDate)
	{
		$return = NULL;
		try
		{
			if(is_int($idSrc))
			{
				if(is_string($geoEntity))
				{
					if(is_string($typeSampling))
					{
						
						if(is_string($senderName))
						{
							if(is_string($creationDate))
							{
								if(!is_null($idGeo = $this->selectIdGeo($geoEntity,$geoEntityType)))
								{
									if(!is_null($idTypeSampling = $this->selectIdTypeSample($typeSampling)))
									{
										$query = "SELECT experience_mod.delete_sampling_all($idSrc,$idGeo,$idTypeSampling,'$senderName','$creationDate') AS return;";
										$arrayQuery = $this->get($query);
										if(intval($arrayQuery[0]["return"])!==1)
										{
											throw new Exception("Delete sampling $nameSampling failed.");
										}
									}
									else
									{
										throw new Exception("Selection of sampling type $typeSampling failed.");
									}
								}
								else
								{
									throw new Exception("Selection of parcel $geoEntity failed.");
								}
							}
							else
							{
								throw new Exception("Wrong argument creationDate $creationDate given.");
							}
						}
						else
						{
							throw new Exception("Wrong argument senderName $senderName given.");
						}
					}
					else
					{
						throw new Exception("Wrong argument typeSampling $typeSampling given.");
					}
				}
				else
				{
					throw new Exception("Wrong argument parcelName $geoEntity given.");
				}
			}
			else
			{
				throw new Exception("Wrong argument idSrc $idSrc given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}
	/*
	*****************             ***************
	**************** MODULE TRAIT ***************
	****************              ***************
	*/

	function insertTrait($nameTrait,$type=NULL)
	{
		//1 if insertion is ok, else NULL
		$return = NULL;
		try
		{
			if(is_string($nameTrait))
			{
				if(!$this->transactionInCourse)
				{
					$this->dbh->beginTransaction();
				}
				if(is_null($type))
				{
					$query = "SELECT trait_mod.trait_s_i_u('$nameTrait','') AS id_trait;";
				}
				else
				{
					$query = "SELECT trait_mod.trait_s_i_u('$nameTrait','','none','$type') AS id_trait;";
				}
				$arrayQuery = $this->get($query);
				if(!is_null($arrayQuery[0]["id_trait"]))
				{
					$return = 1;
				}
				else
				{
					throw new Exception("Insertion of coded trait value failed.");
				}
				if(!$this->transactionInCourse)
				{
					$this->dbh->commit();
				}
			}
			else
			{
				throw new Exception("Wrong argument nameTrait $nameTrait given.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($return);
	}
	
	function selectAllTrait($fonction=NULL)
	{
		$arrayNameTrait = NULL;
		try
		{
			$query = "SELECT trait_mod.select_all_name_trait() AS name_trait;";
			$arrayQuery = $this->get($query);
			if(count($arrayQuery)>0)
			{
				$arrayNameTrait = $arrayQuery;
			}
			else
			{
				throw new Exception("No trait in database.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayNameTrait);
	}
	
	/**
	 * TODO
	 * Insert trait value
	 * @throws Exception
	 */
	function insertTraitValue($myTraitValue,$embargo=NULL,$creationDate=NULL)
	{
		$idTraitValue = NULL;
		try
		{
			if(is_a($myTraitValue,'TraitValue'))
			{
				$idSource = $myTraitValue->get("id_source");
				//insert with NULL reference in the source to say it is global
				if(is_null($idDbDataRef=$this->selectIdRef($idSource,NULL)))
				{
					throw new Exception("No global reference in source $idSource.");
				}
				$nameTaxon = $myTraitValue->get("taxon_name");
				if(is_null($idTaxon=$this->selectIdRealTaxon($nameTaxon)))
				{
					print("Taxon $nameTaxon doesn't exist in database.");
					throw new Exception("Taxon $nameTaxon doesn't exist in database.");
				}
				$nameTrait = $myTraitValue->get("trait_name");
				if(is_null($idTrait=$this->selectIdTrait($nameTrait)))
				{
					print("Trait $nameTrait doesn't exist in database.");
					throw new Exception("Trait $nameTrait doesn't exist in database.");
				}
				$nameSample = $myTraitValue->get("name_sample");
				$value = $myTraitValue->get("raw_trait_value");
				$typeSqlValue = $this->selectTraitSqlType($idTrait);
				if($this->checkTypeValue($value,$typeSqlValue))
				{
					if(is_null($nameSample))
					{
						//insertion of raw trait
						$query = "SELECT trait_mod.raw_trait_value_s_i($idTrait,$idDbDataRef,$idTaxon,'$value') AS id_val;";
						$arrayQuery = $this->get($query);
						if(!is_null($arrayQuery[0]["id_val"]))
						{
							$idTraitValue = intval($arrayQuery[0]["id_val"]);
							//insertion of creation date if any
							if(!is_null($creationDate))
							{
								$this->insertMetadata("creation date",$idTraitValue,$creationDate);
							}
							//insertion of coded trait
							$nameTraitCoded = $myTraitValue->get("attribute_trait");
							$valueCoded = $myTraitValue->get("coded_trait_value");
							$coder = $myTraitValue->get("coder");
							if(!is_null($nameTraitCoded)&&!is_null($valueCoded)&&!is_null($coder))
							{
								if(is_null($idTraitCoded=$this->selectIdTrait($nameTraitCoded)))
								{
									print("Trait $nameTraitCoded doesn't exist in database.");
									throw new Exception("Trait $nameTraitCoded doesn't exist in database.");
								}
								$typeSqlCodedValueTrait = $this->selectTraitSqlType($idTraitCoded);
								//interval trait are not trait coded
								if($typeSqlCodedValueTrait=='interval')
								{
									$query = "SELECT trait_interval_value AS val, trait_interval_value_id AS id FROM trait_mod.interval_raw_trait_value_s_i($idTraitCoded,$idTraitValue,$idTaxon,'$valueCoded','$coder');";
									$arrayQuery = $this->get($query);
									if(!is_null($arrayQuery[0]["id"]))
									{
										$traitIntervalValue = $arrayQuery[0]["val"];
										$idTraitCodedValue = intval($arrayQuery[0]["id"]);
										if($valueCoded!=$traitIntervalValue)
										{
											print("Interval trait $nameTraitCoded already exists for raw trait $nameTrait and coder $coder.");
											throw new Exception("Interval trait $nameTraitCoded already exists for raw trait $nameTrait and coder $coder.");
										}
									}
									else
									{
										throw new Exception("Insertion of interval trait value failed.");
									}
								}
								else
								{
									$query = "SELECT trait_coded_value AS val, trait_coded_value_id AS id FROM trait_mod.select_coded_trait_value($idTraitCoded,$idTraitValue,'$coder');";
									$arrayQuery = $this->get($query);
									if(!is_null($arrayQuery[0]["val"]))
									{
										$traitCodedValue = intval($arrayQuery[0]["val"]);
										$idTraitCodedValue = intval($arrayQuery[0]["id"]);
										if($valueCoded!=$traitCodedValue)
										{
											print("Coded trait $nameTraitCoded already exists for raw trait $nameTrait and coder $coder.");
											throw new Exception("Coded trait $nameTraitCoded already exists for raw trait $nameTrait and coder $coder.");
										}
									}
									else
									{
										$query = "SELECT trait_mod.insert_coded_trait_value($idTraitCoded,$idTraitValue,$valueCoded,'$coder') AS id_val;";
										$arrayQuery = $this->get($query);
										if(!is_null($arrayQuery[0]["id_val"]))
										{
											$idTraitCodedValue = intval($arrayQuery[0]["id_val"]);
											//metadata
											if(!is_null($creationDate))
											{
												$this->insertMetadata("creation date",$idTraitCodedValue,$creationDate);
											}
										}
										else
										{
												
											throw new Exception("Insertion of coded trait value failed.");
										}
									}
								}
								//copyright
								if(!is_null($embargo))
								{
									$value1 = $embargo;
									$value2 = $coder;
									$value3 = $creationDate;
									if(!is_null($this->selectCopyright($idTraitCodedValue)))
									{
										if($value1=='0')
										{
											$this->insertCopyright($idTraitCodedValue,$value1,$value2,$value3);
										}
										$this->insertCopyrightInfo($idTraitCodedValue,$value1,$value2,$value3);
									}
									else
									{
										$this->insertCopyright($idTraitCodedValue,$value1,$value2,$value3);
									}
								}
							}
						}
						else
						{
							throw new Exception("Insertion of raw trait value failed.");
						}
					}
					else
					{
						if(!is_null($idSample=$this->selectIdObjectRef($idSource,$nameSample)))
						{
							$nbInd = $myTraitValue->get("individual_id_number");
							//insertion of raw trait
							$query = "SELECT trait_mod.exp_raw_trait_value_s_i_u($idTrait,$idSample,$nbInd,$idTaxon,'$value') AS id_val;";
							$arrayQuery = $this->get($query);
							if(!is_null($arrayQuery[0]["id_val"]))
							{
								$idTraitValue = intval($arrayQuery[0]["id_val"]);
							}
							else
							{
								throw new Exception("Insertion of raw trait value failed.");
							}
						}
						else
						{
							print("Sample $nameSample doesn't exist in database.");
							throw new Exception("Sample $nameSample doesn't exist in database.");
						}
					}
				}
				else
				{
					throw new Exception("Wrong SQL type for measure $nameTrait value $value.");
				}
			}
			else
			{
				throw new Exception("Wrong argument myTraitValue passed in argument.",1012);
			}
		}
		catch(Exception $e)
		{
			$idTraitValue = NULL;
			if($this->debug)
			{
				$this->trackException($e);
			}
		}
		return($idTraitValue);
	}
	
	function selectTraitSqlType($idTrait)
	{
		$value = NULL;
		try
		{
			if(is_int($idTrait))
			{
				$query = "SELECT trait_mod.select_sql_type_trait($idTrait) AS sql_type;";
				$arrayQuery = $this->get($query);
				if(!is_null($arrayQuery[0]["sql_type"]))
				{
					$value = $arrayQuery[0]["sql_type"];
				}
				else
				{
					throw new Exception("No sql type for trait $idTrait.");
				}
			}
			else
			{
				throw new Exception("Wrong type for id trait $idTrait.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($value);
	}
	
	
	/**
	 * Select trait database identifier from its name
	 * @param string $nameTrait Name of trait of interest
	 * @throws Exception
	 * @return integer
	 */
	function selectIdTrait($nameTrait)
	{
		$idTrait = NULL;
		try
		{
			if(is_string($nameTrait))
			{
				$query = "SELECT trait_mod.select_trait('$nameTrait') AS id_trait;";
				$arrayQuery = $this->get($query);
				if(!is_null($arrayQuery[0]["id_trait"]))
				{
					$idTrait = $arrayQuery[0]["id_trait"];
				}
				else
				{
					//throw new Exception("No trait $nameTrait in database.");
				}
			}
			else
			{
				throw new Exception("Wrong argument nameTrait $nameTrait passed in argument.");
			}
		}
		catch(Exception $e)
		{
			if($this->debug)
			{
				print($e->getMessage()."<br />");
				print($e->getTraceAsString()."<br />");
			}
		}
		return($idTrait);
	}
	
	function selectTraitValueLine($arrayTrait,$arrayTaxon,$type='literature',$optim=TRUE,$isAdmin=0,$owner=NULL)
	{
		$arrayTraitValue = NULL;
		try
		{	
			if(is_array($arrayTrait)&&count($arrayTrait)>0)
			{
				if(is_array($arrayTaxon)&&count($arrayTaxon)>0)
				{
					if($type=='literature')
					{
						$query = "SELECT taxonomy_mod.select_taxo_name(taxon_id) AS name_taxon, trait_mod.select_trait_name(trait_id) AS name_trait, ".
								"trait_value AS raw_trait_value, trait_mod.select_trait_name(trait_coded_id) AS attribute_trait, coded_trait_value, ".
								"(reference_mod.select_publication((reference_mod.select_obj_source_id(trait_value_id)))).*, ".
								"reference_mod.select_source_type_name(reference_mod.select_obj_source_id(trait_value_id)) AS type_source ".
							"FROM trait_mod.trait_value ".
								"LEFT JOIN (SELECT trait_value_id, trait_id AS trait_coded_id,  AVG(trait_coded_value) AS coded_trait_value ".
										"FROM trait_mod.trait_coded_value ";
						if($isAdmin==0)
						{
							$query .= "WHERE (copyright_mod.select_copyright(trait_coded_value_id)=1 OR (copyright_mod.select_copyright(trait_coded_value_id)=0 AND copyright_mod.select_copyright_owner(trait_coded_value_id) ILIKE '$owner')) ";
						}
						$query .= "GROUP BY trait_value_id, trait_id) AS trait_coded USING(trait_value_id) ".
							"WHERE sample_id IS NULL ";
						$query .= " AND taxon_id IN(";
						$lastTaxon = array_pop($arrayTaxon);
						if(count($arrayTaxon)>0)
						{
							foreach($arrayTaxon as $taxon)
							{
								$query .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$taxon') UNION ";
							}
						}
						$query .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$lastTaxon')) ";
						$query .= "AND trait_id IN(";
						$lastTrait = array_pop($arrayTrait);
						if(count($arrayTrait)>0)
						{
							foreach($arrayTrait as $trait)
							{
								$query .= "trait_mod.select_trait('$trait'), ";
							}
						}
						$query .= "trait_mod.select_trait('$lastTrait')) ";
						if($isAdmin==0)
						{
							$query .= "AND (copyright_mod.select_copyright(trait_value_id)=1 OR (copyright_mod.select_copyright(trait_value_id)=0 AND copyright_mod.select_copyright_owner(trait_value_id) ILIKE '$owner')) ";
						}

						$query .= "ORDER BY taxon_id, trait_id;";
					}
					else
					{
						if($type=='experimental')
						{
							$query = "SELECT taxonomy_mod.select_taxo_name(taxon_id) AS name_taxon, trait_mod.select_trait_name(trait_id) AS name_trait, ".
									"MIN(CAST(trait_value AS FLOAT)) AS raw_trait_value_min,MAX(CAST(trait_value AS FLOAT)) AS raw_trait_value_max, ".
									"COUNT(trait_value_id) AS measurement_number, ".
									"site_mod.select_geo_entity_name(geographical_entity_id) AS name_parcel, ".
									"experience_mod.select_sample_year(sample_id,date_mod.select_date_type('sampling_date_start')) AS year_trait, ".
									"my_string_agg(CAST(reference_mod.select_obj_source_id(sample_id) AS VARCHAR)) AS source_list ".
								"FROM trait_mod.trait_value ".
									"JOIN trait_mod.trait USING(trait_id) ".
									"JOIN experience_mod.sample USING(sample_id) ".
									"WHERE sample_id IS NOT NULL ";
							$query .= " AND taxon_id IN(";
							$lastTaxon = array_pop($arrayTaxon);
							if(count($arrayTaxon)>0)
							{
								foreach($arrayTaxon as $taxon)
								{
									$query .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$taxon') UNION ";
								}
							}
							$query .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$lastTaxon')) ";
							$query .= "AND trait_id IN(";
							$lastTrait = array_pop($arrayTrait);
							if(count($arrayTrait)>0)
							{
								foreach($arrayTrait as $trait)
								{
									$query .= "trait_mod.select_trait('$trait'), ";
								}
							}
							$query .= "trait_mod.select_trait('$lastTrait')) ";
							if($isAdmin==0)
							{
								$query .= "AND (copyright_mod.select_copyright(sampling_id)=1 OR (copyright_mod.select_copyright(sampling_id)=0 AND copyright_mod.select_copyright_owner(sampling_id) ILIKE '$owner')) ";
							}
							$query .= "GROUP BY taxon_id,trait_id,name_parcel, experience_mod.select_sample_year(sample_id,date_mod.select_date_type('sampling_date_start')) ";
							$query .= "ORDER BY taxon_id, trait_id;";
						}
						else
						{
							if($type=='soil_pref')
							{
								if(!$optim)
								{
									$query = "SELECT taxonomy_mod.select_taxo_name(taxon_id) AS name_taxon, ".
											"experience_mod.select_sample_year(soil.sample_id,date_mod.select_date_type('sampling_date_start')) AS year_parcel, ".
											"site_mod.select_geo_entity_name(geographical_entity_id) AS name_parcel, ".
											"experiment_measure_code AS measure,MIN(CAST(sample_measure_value AS float)) AS min_measure, ".
											"MAX(CAST(sample_measure_value AS float)) AS max_measure, COUNT(DISTINCT soil.sample_id) AS measurement_number, ".
											"(experience_mod.select_sample_measure(soil.sample_id,experience_mod.select_type_mesure_exp('sample_layer', ".
											"experience_mod.select_type_mesure('sample')))).measure_value AS sample_layer, ".
											"my_string_agg(CAST(reference_mod.select_obj_source_id(soil.sample_id) AS VARCHAR)) AS source_list, ".
											"my_string_agg(CAST(reference_mod.select_obj_source_id(fauna.sample_id) AS VARCHAR)) AS source_list_fauna ".
											"FROM experience_mod.sample soil ".
											"JOIN experience_mod.sample_measure USING(sample_id) ".
											"JOIN experience_mod.experiment_measure USING(experiment_measure_id) ".
											"JOIN experience_mod.sample fauna USING(geographical_entity_id) ".
											"JOIN experience_mod.observation ON(fauna.sample_id=observation.sample_id) ".
										"WHERE experiment_measure_sql_type IN('int4','float8') ".
											"AND experience_mod.select_sample_year(soil.sample_id,date_mod.select_date_type('sampling_date_start'))=experience_mod.select_sample_year(fauna.sample_id,date_mod.select_date_type('sampling_date_start'))".
										" AND taxon_id IN(";
									$lastTaxon = array_pop($arrayTaxon);
									if(count($arrayTaxon)>0)
									{
										foreach($arrayTaxon as $taxon)
										{
											$query .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$taxon') UNION ";
										}
									}
									$query .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$lastTaxon')) ";
									$query .= "AND observation_value>0 ";
									$query .= "AND experiment_measure_code IN(";
									$lastTrait = array_pop($arrayTrait);
									if(count($arrayTrait)>0)
									{
										foreach($arrayTrait as $trait)
										{
											$query .= "'$trait', ";
										}
									}
									$query .= "'$lastTrait') ";
									if($isAdmin==0)
									{
										$query .= "AND (copyright_mod.select_copyright(soil.sampling_id)=1 OR (copyright_mod.select_copyright(soil.sampling_id)=0 AND copyright_mod.select_copyright_owner(soil.sampling_id) ILIKE '$owner')) ";
										$query .= "AND (copyright_mod.select_copyright(fauna.sampling_id)=1 OR (copyright_mod.select_copyright(fauna.sampling_id)=0 AND copyright_mod.select_copyright_owner(fauna.sampling_id) ILIKE '$owner')) ";
									}
									$query .= "GROUP BY taxon_id, geographical_entity_id,experiment_measure_code, ".
											"experience_mod.select_sample_year(soil.sample_id,date_mod.select_date_type('sampling_date_start')), ".
											"(experience_mod.select_sample_measure(soil.sample_id,experience_mod.select_type_mesure_exp('sample_layer',experience_mod.select_type_mesure('sample')))).measure_value; ";
								}
								else
								{
									$query = "SELECT taxonomy_mod.select_taxo_name(taxon_id) AS name_taxon,name_parcel, measure, year_parcel, ".
											"min_measure, max_measure, measurement_number, sample_layer, source_list, ".
											"my_string_agg(CAST(reference_mod.select_obj_source_id(fauna.sample_id) AS VARCHAR)) AS source_list_fauna ";
									if($isAdmin==0)
									{
										$query .= "FROM trait_mod.trait_soil_preference_public ";
									}
									else
									{
										$query .= "FROM trait_mod.trait_soil_preference ";
									}
									$query .= "JOIN experience_mod.sample fauna ON(id_parcel=geographical_entity_id) ".
											"JOIN experience_mod.observation ON(fauna.sample_id=observation.sample_id) ".
										"WHERE taxon_id IN(";
									$lastTaxon = array_pop($arrayTaxon);
									if(count($arrayTaxon)>0)
									{
										foreach($arrayTaxon as $taxon)
										{
											$query .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$taxon') UNION ";
										}
									}
									$query .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$lastTaxon')) ";
									$query .= "AND experience_mod.select_sample_year(fauna.sample_id,date_mod.select_date_type('sampling_date_start'))=year_parcel ";
									$query .= "AND observation_value>0 ";
									$query .= "AND measure IN(";
									$lastTrait = array_pop($arrayTrait);
									if(count($arrayTrait)>0)
									{
										foreach($arrayTrait as $trait)
										{
											$query .= "'$trait', ";
										}
									}
									$query .= "'$lastTrait') ";
									$query .= "GROUP BY taxon_id, name_parcel,year_parcel,min_measure, max_measure, measure, name_taxon, measurement_number, sample_layer, source_list ;";
								}
							}
							else
							{
								if($type=='plot_pref')
								{
									$query = "SELECT taxonomy_mod.select_taxo_name(taxon_id) AS name_taxon".
											", site_mod.select_geo_entity_name(geographical_entity_id) AS name_parcel".
											", geographical_entity_measure_name AS measure, geographical_entity_measure_date_value AS year_parcel".
											", MIN(CAST(geographical_entity_measure_value AS float)) AS min_measure".
											", MAX(CAST(geographical_entity_measure_value AS float)) AS max_measure".
											", COUNT(DISTINCT sample_id) AS measurement_number".
											", my_string_agg(CAST(reference_mod.select_obj_source_id(sample_id) AS VARCHAR)) AS source_list ".
										" FROM site_mod.geographical_entity_measure_value".
											" JOIN site_mod.geographical_entity_measure USING(geographical_entity_measure_id)".
											" JOIN site_mod.geographical_entity_measure_date USING(geographical_entity_measure_value_id)".
											" JOIN experience_mod.sample USING(geographical_entity_id)".
											" JOIN experience_mod.observation USING(sample_id)".
										" WHERE geographical_entity_measure_sql_type IN('float8','int4')".
											" AND geographical_entity_measure_unit!='unitless'".
											" AND CAST(geographical_entity_measure_date_value AS INTEGER)=experience_mod.select_sample_year(sample_id,date_mod.select_date_type('sampling_date_start'))".
									$query .= " AND taxon_id IN(";
									$lastTaxon = array_pop($arrayTaxon);
									if(count($arrayTaxon)>0)
									{
										foreach($arrayTaxon as $taxon)
										{
											$query .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$taxon') UNION ";
										}
									}
									$query .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$lastTaxon')) ";
									$query .= "AND observation_value>0 ";
									$query .= "AND geographical_entity_measure_name IN(";
									$lastTrait = array_pop($arrayTrait);
									if(count($arrayTrait)>0)
									{
										foreach($arrayTrait as $trait)
										{
											$query .= "'$trait', ";
										}
									}
									$query .= "'$lastTrait') ";
									$query .= " GROUP BY taxon_id,geographical_entity_id,geographical_entity_measure_name,geographical_entity_measure_date_value,".
											" date_entity_id,date_type_id;";
								}
								else
								{
									if($type=='interval')
									{
										$query = "SELECT taxonomy_mod.select_taxo_name(taxon_id) AS name_taxon, trait_mod.select_trait_name(trait_id) AS name_trait, ".
										"trait_value AS raw_trait_value, trait_mod.select_trait_name(trait_coded_id) AS attribute_trait, coded_trait_value, ".
										"(reference_mod.select_publication((reference_mod.select_obj_source_id(trait_value_id)))).*, ".
										"reference_mod.select_source_type_name(reference_mod.select_obj_source_id(trait_value_id)) AS type_source ".
										"FROM trait_mod.trait_value rtrait ".
										"LEFT JOIN ( ".
										"SELECT trait_value_father_id, trait_id AS trait_coded_id, my_string_agg(trait_value) AS coded_trait_value ".
										"FROM trait_mod.trait_value ".
										"WHERE trait_value_father_id IS NOT NULL ";
										if($isAdmin==0)
										{
											$query .= "AND (copyright_mod.select_copyright(trait_coded_value_id)=1 OR (copyright_mod.select_copyright(trait_value_id)=0 AND copyright_mod.select_copyright_owner(trait_value_id) ILIKE '$owner')) ";
										}
										$query .= "GROUP BY trait_value_father_id, trait_id) AS itrait ON(rtrait.trait_value_id=itrait.trait_value_father_id) ";
										$query .= "WHERE sample_id IS NULL AND rtrait.trait_value_father_id IS NULL ";
										$query .= " AND taxon_id IN(";
										$lastTaxon = array_pop($arrayTaxon);
										if(count($arrayTaxon)>0)
										{
											foreach($arrayTaxon as $taxon)
											{
												$query .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$taxon') UNION ";
											}
										}
										$query .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$lastTaxon')) ";
										$query .= "AND trait_id IN(";
										$lastTrait = array_pop($arrayTrait);
										if(count($arrayTrait)>0)
										{
											foreach($arrayTrait as $trait)
											{
												$query .= "trait_mod.select_trait('$trait'), ";
											}
										}
										$query .= "trait_mod.select_trait('$lastTrait')) ";
										if($isAdmin==0)
										{
											$query .= "AND (copyright_mod.select_copyright(rtrait.trait_value_id)=1 OR (rtrait.copyright_mod.select_copyright(trait_value_id)=0 AND copyright_mod.select_copyright_owner(trait_value_id) ILIKE '$owner')) ";
										}
										$query .= "ORDER BY taxon_id, trait_id;";
									}
									else
									{
										throw new Exception("Wrong type $type given.");
									}
								}
							}
						}
					}
					$arrayQuery = $this->get($query);
					if(count($arrayQuery)>0&&!is_null($arrayQuery[0]["name_taxon"]))
					{
						$arrayTraitValue = $arrayQuery;
					}
					else
					{
						throw new Exception("No literature trait value in database.");
					}
				}
				else
				{
					throw new Exception("Wrong argument $arrayTrait passed in argument.");
				}
			}
			else
			{
				throw new Exception("Wrong argument $arrayTrait passed in argument.");
			}
		}
		catch(Exception $e)
		{
			if($this->debug)
			{
				print($e->getMessage()."<br />");
				print($e->getTraceAsString()."<br />");
			}
		}
		return($arrayTraitValue);
	}

	function selectTraitValueLineExpSyn($arrayTrait,$arrayTaxon,$isAdmin=0,$owner=NULL)
	{
		$arrayTraitValue = NULL;
		try
		{
			if(is_array($arrayTrait)&&count($arrayTrait)>0)
			{
				if(is_array($arrayTaxon)&&count($arrayTaxon)>0)
				{
					$query = "SELECT taxonomy_mod.select_taxo_name(taxon_id) AS name_taxon, ".
							"MIN(CAST(trait_value AS FLOAT)) AS raw_trait_value_min,MAX(CAST(trait_value AS FLOAT)) AS raw_trait_value_max, ".
							"COUNT(trait_value_id) AS measurement_number, ".
							"site_mod.select_geo_entity_name(geographical_entity_id) AS name_parcel, ".
							"experience_mod.select_sample_year(sample_id,date_mod.select_date_type('sampling_date_start')) AS year_trait, ".
							"my_string_agg(CAST(reference_mod.select_obj_source_id(sample_id) AS VARCHAR)) AS source_list ".
							"FROM trait_mod.trait_value ".
							"JOIN trait_mod.trait USING(trait_id) ".
							"JOIN experience_mod.sample USING(sample_id) ".
							"WHERE sample_id IS NOT NULL ";
					$query .= " AND taxon_id IN(";
					$lastTaxon = array_pop($arrayTaxon);
					if(count($arrayTaxon)>0)
					{
						foreach($arrayTaxon as $taxon)
						{
							$query .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$taxon') UNION ";
						}
					}
					$query .= "SELECT taxon_id FROM taxonomy_mod.select_taxon_sons_syn('$lastTaxon')) ";
					$query .= "AND trait_id IN(";
					$lastTrait = array_pop($arrayTrait);
					if(count($arrayTrait)>0)
					{
						foreach($arrayTrait as $trait)
						{
							$query .= "trait_mod.select_trait('$trait'), ";
						}
					}
					$query .= "trait_mod.select_trait('$lastTrait')) ";
					if($isAdmin==0)
					{
						$query .= "AND (copyright_mod.select_copyright(sampling_id)=1 OR (copyright_mod.select_copyright(sampling_id)=0 AND copyright_mod.select_copyright_owner(sampling_id) ILIKE '$owner')) ";
					}
					$query .= "GROUP BY taxon_id,name_parcel, experience_mod.select_sample_year(sample_id,date_mod.select_date_type('sampling_date_start')) ";
					$query .= "ORDER BY taxon_id;";
					$arrayQuery = $this->get($query);
					if(count($arrayQuery)>0&&!is_null($arrayQuery[0]["name_taxon"]))
					{
						$arrayTraitValue = $arrayQuery;
					}
					else
					{
						throw new Exception("No literature trait value in database.");
					}
				}
				else
				{
					throw new Exception("Wrong argument $arrayTrait passed in argument.");
				}
			}
			else
			{
				throw new Exception("Wrong argument $arrayTrait passed in argument.");
			}
		}
		catch(Exception $e)
		{
			if($this->debug)
			{
				print($e->getMessage()."<br />");
				print($e->getTraceAsString()."<br />");
			}
		}
		return($arrayTraitValue);
	}
	
	
	/*
	****************             ***************
	**************** MODULE DATE ***************
	****************             ***************
	*/

	function selectMinYear($min=TRUE)
	{
		$year = NULL;
		try
		{
			if($min)
			{
				$query = "SELECT MIN(CAST(sample_date_value AS INT)) AS limit_year 
					FROM experience_mod.sample_date JOIN date_mod.date_entity USING(date_entity_id)
						JOIN date_mod.date_type USING(date_type_id)
					WHERE date_type_name='sampling_date_start' AND date_entity_name='year';";
			}
			else
			{
				$query = "SELECT MAX(CAST(sample_date_value AS INT)) AS limit_year
					FROM experience_mod.sample_date JOIN date_mod.date_entity USING(date_entity_id)
						JOIN date_mod.date_type USING(date_type_id)
					WHERE date_type_name='sampling_date_end' AND date_entity_name='year';";
			}
			$arrayQuery = $this->get($query);
			if(!is_null($arrayQuery[0]["limit_year"]))
			{
				$year = intval($arrayQuery[0]["limit_year"]);
			}
			else
			{
				throw new Exception("No releve dated in the database.",2004);
			}
		}
		catch(Exception $e)
		{
			if($this->debug)
			{
				print($e->getMessage()."<br />");
				print($e->getTraceAsString()."<br />");
			}
		}
		return($year);
	}
	
	/*
	****************               ***************
	**************** MODULE GLOBAL ***************
	****************               ***************
	*/
	//TODO
	function updateSelectionTable()
	{
		$return = NULL;
		try
		{
			$query = "SELECT my_system.optimize_meta_table('measure_geo');";
			$this->get($query);
			$query = "SELECT my_system.optimize_meta_table('releve_parameter');";
			$this->get($query);
		}
		catch(Exception $e)
		{
			if($this->debug)
			{
				print($e->getMessage()."<br />");
				print($e->getTraceAsString()."<br />");
			}
		}
		return($return);
	}
		
	/*
	****************                   ***************
	**************** MODULE UNIVERSE   ***************
	****************                   ***************
	*/
	
	/**
	 * 
	 * Select universe filter
	 * @param string $univ
	 * @throws Exception
	 */
	function getUniverse($univ)
	{
		$arrayUniverse = NULL;
		try
		{
			$query = "SELECT universe_mod.select_universe_table('$univ') AS my_universe;";
			$arrayQuery = $this->get($query);
			if(!is_null($arrayQuery[0]["my_universe"]))
			{
				$arrayUniverse = array();
				foreach($arrayQuery AS $row)
				{
					array_push($arrayUniverse,$row["my_universe"]);
				}
			}
			else
			{
				throw new Exception("No universe element in database.");
			}
		}
		catch(Exception $e)
		{
			if($this->debug)
			{
				print($e->getMessage()."<br />");
				print($e->getTraceAsString()."<br />");
			}
		}
		return($arrayUniverse);
	}
	
	/*
	****************                   ***************
	**************** MODULE STATISTIQUES   ***************
	****************                   ***************
	*/
	
	function selectQuantitativeTraitBySourceStat($idSource)
	{
		$arrayStat = NULL;
		try
		{
			if(is_integer($idSource))
			{
				$query = "SELECT trait, taxon, count(*) AS nb_observation, my_string_agg(coder) ".
					"FROM ( ".
						"SELECT trait_name AS trait, taxon_name AS taxon, REPLACE(my_string_agg(\"owner\"),':','/') AS coder ".
						"FROM trait_mod.trait_value  ".
							"JOIN trait_mod.trait USING(trait_id) ".
							"JOIN taxonomy_mod.taxonomy USING(taxon_id) ".
							"JOIN copyright_mod.copyright ON(trait_value_id=global_identifier_id) ".
							"JOIN copyright_mod.copyright_info USING(global_identifier_id) ".
						"WHERE trait_sql_type IN('float8','int4') ".
							"AND reference_mod.select_obj_source_id(trait_value_id)=$idSource ".
						"GROUP BY trait_value_id, trait_name, taxon_name ".
						"ORDER BY trait_name, taxon_name ".
						") AS foo ".
					"GROUP BY trait, taxon ORDER BY trait, taxon;";
				$arrayQuery = $this->get($query);
				if(count($arrayQuery)>0)
				{
					$arrayStat = $arrayQuery;
				}
			}
			else
			{
				throw new Exception("Wrong type for source identifier $idSource.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayStat);
	}

	function selectQualitativeTraitBySourceStat($idSource)
	{
		$arrayStat = NULL;
		try
		{
			if(is_integer($idSource))
			{
				$query = "SELECT trait, taxon, count(*) AS nb_observation, my_string_agg(coder) AS coder ".
					"FROM ( ".
						"SELECT trait_name AS trait, taxon_name AS taxon, REPLACE(my_string_agg(\"owner\"),':','/') AS coder ".
						"FROM trait_mod.trait_value ".
						"JOIN trait_mod.trait traw USING(trait_id) ".
						"JOIN taxonomy_mod.taxonomy USING(taxon_id) ".
						"JOIN copyright_mod.copyright_info iraw ON(trait_value_id=global_identifier_id) ".
						"WHERE trait_sql_type IN('varchar') ".
							"AND reference_mod.select_obj_source_id(trait_value_id)=$idSource GROUP BY trait_value_id, trait_name, taxon_name ORDER BY trait_name, taxon_name ".
						") AS foo ".
					"GROUP BY trait, taxon ORDER BY trait, taxon;";
				$arrayQuery = $this->get($query);
				if(count($arrayQuery)>0)
				{
					$arrayStat = $arrayQuery;
				}
			}
			else
			{
				throw new Exception("Wrong type for source identifier $idSource.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayStat);
	}
	
	/**
	 * Select number of coded trait by source, trait and taxon with coders name
	 * @param integer $idSource
	 * @param string $trait
	 * @param string $taxon
	 * @throws Exception
	 */
	function selectCodedTraitBySourceStat($idSource,$trait,$taxon)
	{
		$arrayStat = NULL;
		try
		{
			if(is_integer($idSource))
			{
				if(is_string($trait))
				{
					if(is_string($taxon))
					{
						$query = "SELECT ctrait.trait_name AS coded_trait, count(*) AS nb_observation ".
							"FROM trait_mod.trait_coded_value ".
								"JOIN trait_mod.trait ctrait USING(trait_id) ".
								"JOIN trait_mod.trait_value rval USING(trait_value_id) ".
								"JOIN trait_mod.trait rtrait ON(rtrait.trait_id=rval.trait_id) ".
								"JOIN taxonomy_mod.taxonomy USING(taxon_id) ".
								"LEFT JOIN copyright_mod.copyright_info ON(global_identifier_id=trait_coded_value_id) ".
							"WHERE rtrait.trait_sql_type IN('varchar') ".
								"AND reference_mod.select_obj_source_id(trait_value_id)=$idSource ".
								"AND taxon_name='$taxon' AND rtrait.trait_name='$trait' ".
								"GROUP BY coded_trait";
						$arrayQuery = $this->get($query);
						if(count($arrayQuery)>0)
						{
							$arrayStat = $arrayQuery;
						}
					}
					else
					{
						throw new Exception("Wrong type for taxon $taxon.");
					}
				}
				else
				{
					throw new Exception("Wrong type for trait $trait.");
				}
			}
			else
			{
				throw new Exception("Wrong type for source identifier $idSource.");
			}
		}
		catch(Exception $e)
		{
			$this->trackException($e);
		}
		return($arrayStat);
	}
}
?>
