<?php 

/**
* Create a meta data set of insertion file
*/
class MetaData
{
	
    /* All the field of this class have the same name of the meta data header of the files */

	/**
	 * 
	 * @var
	 */
	private $dataSetName;

	/**
	 * 
	 * @var
	 */
	private $creationDate;
	
	/**
	 * 
	 * @var
	 */
	private $dataOwner;
	
	/**
	 * 
	 * @var
	 */
	private $dataProvider;
	
	/**
	 * 
	 * @var
	 */
	private $contactName;
	
	/**
	 * 
	 * @var
	 */
	private $eMail;

	/**
	 * 
	 * @var
	 */
	private $collector;

	/**
	 * 
	 * @var
	 */
	private $identifier;

	/**
	 * 
	 * @var
	 */
	private $coder;

	/**
	 * 
	 * @var
	 */
	private $project;

	/**
	 * 
	 * @var
	 */
	private $author_s;

	/**
	 * @var
	 */
	private $publicationYear;

	/**
	 * 
	 * @var
	 */
	private $title;

	/**
	 * 
	 * @var
	 */
	private $edition;

	/**
	 * 
	 * @var
	 */
	private $referenceType;

	/**
	 * 
	 * @var
	 */
	private $publicationRepository;

	/**
	 * 
	 * @var
	 */
	private $availability;

	/**
	 * This constructor contains data type checkings and constraint filters 
	 * 
	 */
	function __construct(
		$dataSetName,
		$creationDate,
		$dataOwner,
		$dataProvider,
		$contactName,
		$eMail,
		$collector,
		$identifier,
		$coder,
		$project,
		$author_s,
		$publicationYear,
		$title,
		$edition,
		$referenceType,
		$publicationRepository,
		$availability,
        $noCheck=NULL) 
    {
        //Default values of references
        if(!$author_s) $author_s=$dataOwner;
        if(!$publicationYear)
        {
            preg_match('#.*/.*/(.*)#',$creationDate,$capture);
            $publicationYear=$capture[1];
        } 
        if(!$title) $title=$dataSetName;
        if(!$edition) $edition='IRBAS database';
        if(!$referenceType) $referenceType='Unpublished work';

        if(!$noCheck)
        {
            //check metada needed are presents (name, creation, provider)
            Self::checkNeededMetaData(
            $dataSetName,
            $creationDate,
            $dataOwner,
            $dataProvider,
            $contactName,
            $eMail,
            $collector,
            $identifier,
            $coder,
            $project,
            $author_s,
            $publicationYear,
            $title,
            $edition,
            $referenceType,
            $publicationRepository,
            $availability);

            $this->dataSetName = $dataSetName;
            
            /* Creation_date content must match with the pattern DD/MM/YYYY */
            if(isset($creationDate))
            {
                Self::checkCreationDate($creationDate);
            }
            
            $this->dataOwner = $dataOwner;

            $this->dataProvider = $dataProvider;

            $this->contactName = $contactName;

            /* Contact_email content must match with the pattern __@_._ */
            if(isset($eMail))
            {
                Self::checkEmail($eMail);   
            }
            
            $this->collector = $collector;


            $this->identifier = $identifier;


            $this->coder = $coder;

            $this->project = $project;

        
            $this->author_s = $author_s;

            if($publicationYear)
            {
                if(is_numeric($publicationYear))
                {
                    $this->publicationYear = $publicationYear;
                } else {
                    if(is_array($publicationYear)){
                        foreach ($publicationYear as $value) 
                        {
                            if(!is_numeric($value))
                            {
                                throw new Exception("Publication year must be a number in the format: yyyy. 
                                Please correct, re-save and re-insert.");
                            }
                        }
                        $this->publicationYear = $publicationYear;
                    }else{
                        throw new Exception("Publication year must be a number in the format: yyyy. 
                        Please correct, re-save and re-insert.");
                    }   
                }
            }

            
            $this->title = $title;

            $this->edition = $edition;
            
            if(is_array($referenceType))
            {
                foreach ($referenceType as $key => $value)
                {
                    apiPHP::checkVocabulary(Conf::$metaDataVocabulary,'reference_type',$value);
                }
            }else{
                apiPHP::checkVocabulary(Conf::$metaDataVocabulary,'reference_type',$referenceType);
            }
            

            $this->referenceType = $referenceType;

            $this->publicationRepository = $publicationRepository;

            /* Availability content has to be 0 or 1. */
            if(isset($availability))
            {
                if(is_numeric($availability))
                {
                    if($availability == 0 || $availability== 1)
                    {
                        $this->availability = $availability;
                    } else {
                        throw new Exception("You must enter a valid \”availability\” value. It must be a 0 or 1 only.
                        Please correct, re-save and re-insert.");
                    }
                } else {
                    if(is_array($availability))
                    {
                        if($availability[0] == 0 || $availability[0]== 1)
                        {
                            $this->$availability = $availability[0];
                        } else {
                            throw new Exception("You must enter a valid \”availability\” value. It must be a 0 or 1 only.
                            Please correct, re-save and re-insert.");
                        }    
                    }else{
                        throw new Exception("You must enter a valid \”availability\” value. It must be a 0 or 1 only.
                        Please correct, re-save and re-insert.");
                    }
                } 
            }else{
                $this->$availability= NULL;
            }

        } else {
            $this->dataSetName = $dataSetName;
            $this->creationDate = $creationDate;
            $this->dataOwner = $dataOwner;
            $this->dataProvider = $dataProvider;
            $this->contactName = $contactName;
            $this->eMail = $eMail;
            $this->collector = $collector;
            $this->identifier = $identifier;
            $this->coder = $coder;
            $this->project = $project;
            $this->author_s = $author_s;
            $this->publicationYear = $publicationYear;
            $this->title = $title;
            $this->edition = $edition;
            $this->referenceType = $referenceType;
            $this->publicationRepository = $publicationRepository;
            
            if(isset($availability))
            {
                if(is_numeric($availability))
                {
                    $this->availability = $availability;
                } else {
                    if(is_array($availability))
                    {
                        $this->availability = $availability[0];
                    }else{
                        $this->availability=$availability;
                    }
                }
            }else{
                $this->availability=$availability;
            }
		}
	}

	/**
	 * Read configuration file that contains needed MetaData
	 * Check during instantiation if a needed fields are define
	 * 
	 * @var all class field
	 */
	static private function checkNeededMetaData($dataSetName,
		$creationDate,
		$dataOwner,
		$dataProvider,
		$contactName,
		$eMail,
		$collector,
		$identifier,
		$coder,
		$project,
		$author_s,
		$publicationYear,
		$title,
		$edition,
		$referenceType,
		$publicationRepository,
		$availability) 
	{
        if(is_array($author_s)||is_array($publicationYear)||is_array($title)||is_array($edition)
            ||is_array($referenceType))
        {
            $numberOfReference=count($title);
        }else{
            $numberOfReference=False;
        }
		foreach (Conf::$metaDataMandatoryFields as $key => $value) 
		{
            if($$value==NULL)
            {
                throw new Exception("Error: a mandatory metadata field ($key) is missing. 
                Please correct, re-save and re-insert.");
            }
        }
	}

    /**
     * 
     */
    public function checkReference()
    {
        $functionGetMutipleReferenceMetaData = array('author s'=>'getAuthorS',
            'publication year'=>'getPublicationYear',
            'title'=>'getTitle','edition'=>'getEdition',
            'reference type'=>'getReferenceType');

        foreach ($functionGetMutipleReferenceMetaData as $key => $value) 
        {
            if($getFunction=call_user_func(array($this, $value)))
            {
                if(!is_array($getFunction))
                {
                    $number[$key]=1;
                }else{
                    $number[$key]=count($getFunction);
                }
            }
        }

        if($number['author s']==$number['publication year']&&
            $number['publication year']==$number['title']&&
            $number['title']==$number['reference type']&&
            $number['reference type']==$number['edition'])
        {

        }else{
            throw new Exception("Each reference must consist of an author(s), a publication year, a title, an edition and a reference type.
            One or more of these mandatory fields is missing. Please correct, re-save and re-insert.");
            
        }
    }

	/**
	 * 
	 * @var
	 */
	private function checkEmail($eMail) 
	{

		if(!filter_var($eMail, FILTER_VALIDATE_EMAIL)) 
		{
			throw new Exception("The email address is not valid. Please check, correct, re-save and re-insert.");
		} 
		else 
		{
			$this->eMail = $eMail;
		}

	}

	/**
	 * 
	 * @var
	 */
	private function checkCreationDate($creationDate) 
	{
		if(preg_match("#^(0[1-9]|[1-2][0-9]|3[0-1])/(0[1-9]|1[0-2])/[0-9]{4}$#",$creationDate)) 
		{
			$this->creationDate = $creationDate;
		}
		else
		{	
			throw new Exception("The creation-date is not valid (e.g. it is formatted incorrectly).
            Please correct, re-save and re-insert. You must use the dd/mm/yyyy format.");
		}
	}

	/**
	 * 
	 * @var
	 */
	private function checkAvailability($availability) 
	{

		if(is_numeric($availability))
		{
			if($availability == 0 || $availability== 1)
		 	{
				$this->availability = $availability;
			} else {
				throw new Exception("You must enter a valid \”availability\” value. It must be a 0 or 1 only.
                Please correct, re-save and re-insert.");
			}
		} else {
			if(is_array($availability))
			{

				if($availability[0] == 0 || $availability[0]== 1)
                {
                    $this->$availability = $availability[0];
                } else {
                    throw new Exception("You must enter a valid \”availability\” value. It must be a 0 or 1 only.
                    Please correct, re-save and re-insert.");
                }
				
			}else{
				throw new Exception("You must enter a valid \”availability\” value. It must be a 0 or 1 only.
                Please correct, re-save and re-insert.");
			}
		} 	
	}

	/**
	 * 
	 * @var MetaData object to be compared with
	 */
	public function IsSameMetaData($SecondObject)
	{
		if($this == $SecondObject)
		{
			return True;
		}else {
			$arrayThis =  (array) $this;
			$arraySecondObject =  (array) $SecondObject;
			$array_diff= array_diff_assoc($arrayThis, $arraySecondObject);
			$display_array_diff= array_keys($array_diff);

			throw new Exception("Error: the metadata in the Site and Environment and/or Fauna files are different.
            The first 17 rows of metadata must be the same in any Site, Fauna and/or Environment file associated with the same dataset.
            Please correct, re-save and re-insert. Note, the differences are on: ". print_r($display_array_diff) );
		}
	}


    /**
     * Gets the value of dataSetName.
     *
     * @return mixed
     */
    public function getDataSetName()
    {
        return $this->dataSetName;
    }

    /**
     * Sets the value of dataSetName.
     *
     * @param mixed $dataSetName the data set name
     *
     * @return self
     */
    private function _setDataSetName($dataSetName)
    {
        $this->dataSetName = $dataSetName;

        return $this;
    }

    /**
     * Gets the value of creationDate.
     *
     * @return mixed
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * Sets the value of creationDate.
     *
     * @param mixed $creationDate the creation date
     *
     * @return self
     */
    private function _setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;

        return $this;
    }

    /**
     * Gets the value of dataOwner.
     *
     * @return mixed
     */
    public function getDataOwner()
    {
        return $this->dataOwner;
    }

    /**
     * Sets the value of dataOwner.
     *
     * @param mixed $dataOwner the data owner
     *
     * @return self
     */
    private function _setDataOwner($dataOwner)
    {
        $this->dataOwner = $dataOwner;

        return $this;
    }

    /**
     * Gets the value of dataProvider.
     *
     * @return mixed
     */
    public function getDataProvider()
    {
        return $this->dataProvider;
    }

    /**
     * Sets the value of dataProvider.
     *
     * @param mixed $dataProvider the data provider
     *
     * @return self
     */
    private function _setDataProvider($dataProvider)
    {
        $this->dataProvider = $dataProvider;

        return $this;
    }

    /**
     * Gets the value of contactName.
     *
     * @return mixed
     */
    public function getContactName()
    {
        return $this->contactName;
    }

    /**
     * Sets the value of contactName.
     *
     * @param mixed $contactName the contact name
     *
     * @return self
     */
    private function _setContactName($contactName)
    {
        $this->contactName = $contactName;

        return $this;
    }

    /**
     * Gets the value of eMail.
     *
     * @return mixed
     */
    public function getMail()
    {
        return $this->eMail;
    }

    /**
     * Sets the value of eMail.
     *
     * @param mixed $eMail the e mail
     *
     * @return self
     */
    private function _setMail($eMail)
    {
        $this->eMail = $eMail;

        return $this;
    }

    /**
     * Gets the value of collector.
     *
     * @return mixed
     */
    public function getCollector()
    {
        return $this->collector;
    }

    /**
     * Sets the value of collector.
     *
     * @param mixed $collector the collector
     *
     * @return self
     */
    private function _setCollector($collector)
    {
        $this->collector = $collector;

        return $this;
    }

    /**
     * Gets the value of identifier.
     *
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Sets the value of identifier.
     *
     * @param mixed $identifier the identifier
     *
     * @return self
     */
    private function _setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Gets the value of coder.
     *
     * @return mixed
     */
    public function getCoder()
    {
        return $this->coder;
    }

    /**
     * Sets the value of coder.
     *
     * @param mixed $coder the coder
     *
     * @return self
     */
    private function _setCoder($coder)
    {
        $this->coder = $coder;

        return $this;
    }

    /**
     * Gets the value of project.
     *
     * @return mixed
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Sets the value of project.
     *
     * @param mixed $project the project
     *
     * @return self
     */
    private function _setProject($project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Gets the value of author_s.
     *
     * @return mixed
     */
    public function getAuthorS()
    {
        return $this->author_s;
    }

    /**
     * Sets the value of author_s.
     *
     * @param mixed $author_s the author s
     *
     * @return self
     */
    private function _setAuthorS($author_s)
    {
        $this->author_s = $author_s;

        return $this;
    }

    /**
     * Gets the value of publicationYear.
     *
     * @return date number or array of date number
     */
    public function getPublicationYear()
    {
        return $this->publicationYear;
    }

    /**
     * Sets the value of publicationYear.
     *
     * @param date number or array of date number $publicationYear the publication year
     *
     * @return self
     */
    private function _setPublicationYear($publicationYear)
    {
        $this->publicationYear = $publicationYear;

        return $this;
    }

    /**
     * Gets the value of title.
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Sets the value of title.
     *
     * @param mixed $title the title
     *
     * @return self
     */
    private function _setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Gets the value of edition.
     *
     * @return mixed
     */
    public function getEdition()
    {
        return $this->edition;
    }

    /**
     * Sets the value of edition.
     *
     * @param mixed $edition the edition
     *
     * @return self
     */
    private function _setEdition($edition)
    {
        $this->edition = $edition;

        return $this;
    }

    /**
     * Gets the value of referenceType.
     *
     * @return mixed
     */
    public function getReferenceType()
    {
        return $this->referenceType;
    }

    /**
     * Sets the value of referenceType.
     *
     * @param mixed $referenceType the reference type
     *
     * @return self
     */
    private function _setReferenceType($referenceType)
    {
        $this->referenceType = $referenceType;

        return $this;
    }

    /**
     * Gets the value of publicationRepository.
     *
     * @return mixed
     */
    public function getPublicationRepository()
    {
        return $this->publicationRepository;
    }

    /**
     * Sets the value of publicationRepository.
     *
     * @param mixed $publicationRepository the publication repository
     *
     * @return self
     */
    private function _setPublicationRepository($publicationRepository)
    {
        $this->publicationRepository = $publicationRepository;

        return $this;
    }

    /**
     * Gets the value of availability.
     *
     * @return mixed
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * Sets the value of availability.
     *
     * @param mixed $availability the availability
     *
     * @return self
     */
    private function _setAvailability($availability)
    {
        $this->availability = $availability;

        return $this;
    }
}

?>