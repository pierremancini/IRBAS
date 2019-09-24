<?php 

/**
* Creates measurement from fauna, environment or site 
*/
class Measurement
{
	/**
	 * @var the column name giving a type of measurement 
	 */
	private $columnName;

	/**
	 * @var measurement's value
	 */
	private $value;

    /**
     * @var Object liked with this measure 
     */
    private $linkedObject;
	
	/**
	 * @var the first date of the timespan of the measurement 
	 */
	private $firstDateTimeSpan;


    /**
     * @var the last date of the timespan of the measurement 
     */
    private $lastDateTimeSpan;


	/**
	 * @var the frequency of the timespan of the measurement 
	 */
	private $frenquency;


    /**
     * @var Number of samples used to produce the measurement
     */
    private $numberSample;

	
	function __construct($columnName,$value,$linkedObject,$firstDateTimeSpan=NULL,$lastDateTimeSpan=NULL,$frequency=NULL,$numberSample=NULL)
	{
        if(is_a($linkedObject,'GeographicalEntity'))
        {
            $vocabulary=Conf::$siteVocabulary;
            apiPHP::checkVocabulary($vocabulary,$columnName,$value);
            apiPHP::CheckInFork($columnName,$value,'site');
        }

        if(is_a($linkedObject,'Sample'))
        {
            $vocabulary=Conf::$environmentVocabulary;
            apiPHP::checkVocabulary($vocabulary,$columnName,$value);
            apiPHP::checkInFork($columnName,$value,'environment');
        }
		$this->columnName=$columnName;

        //All commas are replaced to point in order to avoid problem due to the use if diffrent .csv editors
        $this->value=str_replace(",",".",$value);

		$this->firstDateTimeSpan=$firstDateTimeSpan;
        $this->lastDateTimeSpan=$lastDateTimeSpan;
		$this->frequency=$frequency;
		$this->linkedObject=$linkedObject;
	}

    /**
     * Gets the value of columnName.
     *
     * @return mixed
     */
    public function getNumberSample()
    {
        return $this->numberSample;
    }


    /**
     * Sets the value of columnName.
     *
     * @param mixed $columnName the column name
     *
     * @return self
     */
    public function setNumberSample($numberSample)
    {
        $this->numberSample = $numberSample;
    }

    /**
     * Gets the value of columnName.
     *
     * @return mixed
     */
    public function getColumnName()
    {
        return $this->columnName;
    }

    /**
     * Sets the value of columnName.
     *
     * @param mixed $columnName the column name
     *
     * @return self
     */
    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;
    }

    /**
     * Gets the value of value.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets the value of value.
     *
     * @param mixed $value the value
     *
     * @return self
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Gets the value of dateTimeSpan.
     *
     * @return mixed
     */
    public function getFirstDateTimeSpan()
    {
        return $this->firstDateTimeSpan;
    }

    /**
     * Sets the value of dateTimeSpan.
     *
     * @param mixed $dateTimeSpan the date time span
     *
     * @return self
     */
    public function setFirstDateTimeSpan($firstDateTimeSpan)
    {
        $this->firstDateTimeSpan = $firstDateTimeSpan;
    }

    /**
     * Gets the value of dateTimeSpan.
     *
     * @return mixed
     */
    public function getLastDateTimeSpan()
    {
        return $this->lastDateTimeSpan;
    }

    /**
     * Sets the value of dateTimeSpan.
     *
     * @param mixed $dateTimeSpan the date time span
     *
     * @return self
     */
    public function setLastDateTimeSpan($lastDateTimeSpan)
    {
        $this->lastDateTimeSpan = $lastDateTimeSpan;
    }


    /**
     * Gets the value of frenquency.
     *
     * @return mixed
     */
    public function getFrequency()
    {
        return $this->frenquency;
    }

    /**
     * Sets the value of frenquency.
     *
     * @param mixed $frenquency the frenquency
     *
     * @return self
     */
    public function setFrequency($frenquency)
    {
        $this->frenquency = $frenquency;
    }

    /**
     * DEPRECATED cf getLinkedObject
     */
    public function getSmallestGeoEntity()
    {
        return $this->linkedObject;
    }

    /**
     * DEPRECATED cf setLinkedObject
     */
    public function setSmallestGeoEntity($linkedObject)
    {
        $this->linkedObject = $linkedObject;
    }

    public function getLinkedObject()
    {
        return $this->linkedObject;
    }

    public function setLinkedObject($linkedObject)
    {
        $this->linkedObject = $linkedObject;
    }
}



?>