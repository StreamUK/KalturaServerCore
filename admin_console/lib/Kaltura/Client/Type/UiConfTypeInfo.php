<?php
/**
 * @package Admin
 * @subpackage Client
 */
class Kaltura_Client_Type_UiConfTypeInfo extends Kaltura_Client_ObjectBase
{
	public function getKalturaObjectType()
	{
		return 'KalturaUiConfTypeInfo';
	}
	
	public function __construct(SimpleXMLElement $xml = null)
	{
		parent::__construct($xml);
		
		if(is_null($xml))
			return;
		
		if(count($xml->type))
			$this->type = (int)$xml->type;
		if(empty($xml->versions))
			$this->versions = array();
		else
			$this->versions = Kaltura_Client_Client::unmarshalItem($xml->versions);
		$this->directory = (string)$xml->directory;
		$this->filename = (string)$xml->filename;
	}
	/**
	 * UiConf Type
	 * 
	 *
	 * @var Kaltura_Client_Enum_UiConfObjType
	 */
	public $type = null;

	/**
	 * Available versions
	 * 
	 *
	 * @var array of KalturaString
	 */
	public $versions;

	/**
	 * The direcotry this type is saved at
	 * 
	 *
	 * @var string
	 */
	public $directory = null;

	/**
	 * Filename for this UiConf type
	 * 
	 *
	 * @var string
	 */
	public $filename = null;


}

