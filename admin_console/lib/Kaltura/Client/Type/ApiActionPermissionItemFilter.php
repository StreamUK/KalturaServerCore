<?php
/**
 * @package Admin
 * @subpackage Client
 */
class Kaltura_Client_Type_ApiActionPermissionItemFilter extends Kaltura_Client_Type_ApiActionPermissionItemBaseFilter
{
	public function getKalturaObjectType()
	{
		return 'KalturaApiActionPermissionItemFilter';
	}
	
	public function __construct(SimpleXMLElement $xml = null)
	{
		parent::__construct($xml);
		
		if(is_null($xml))
			return;
		
	}

}

