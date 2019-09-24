<?php 

/**
* 
*/
class Station extends GeographicalEntity
{


	/**
	 *  Should be used for extraction part. Is not used for insertion part, the code is contained in the name
	 * @var
	 */
	private $code;
	
	function __construct($type,$value,$latitude,$longitude,$son=NULL,$code)
	{
		parent::__construct($type,$value,$latitude,$longitude,$son=NULL);
		$this->code= $code;
	}


    /**
     * Gets the value of code.
     *
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Sets the value of code.
     *
     * @param mixed $code the code
     *
     * @return self
     */
    public function setCode($code)
    {
        $this->code = $code;
    }
}

?>