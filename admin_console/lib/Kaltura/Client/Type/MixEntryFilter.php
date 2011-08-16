<?php
/**
 * @package Admin
 * @subpackage Client
 */
class Kaltura_Client_Type_MixEntryFilter extends Kaltura_Client_Type_MixEntryBaseFilter
{
	public function getKalturaObjectType()
	{
		return 'KalturaMixEntryFilter';
	}
	
	public function __construct(SimpleXMLElement $xml = null)
	{
		parent::__construct($xml);
		
		if(is_null($xml))
			return;
		
	}

}

