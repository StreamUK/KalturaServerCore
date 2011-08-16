<?php
/**
 * @package Admin
 * @subpackage Client
 */
class Kaltura_Client_ContentDistribution_Type_GenericDistributionProviderFilter extends Kaltura_Client_ContentDistribution_Type_GenericDistributionProviderBaseFilter
{
	public function getKalturaObjectType()
	{
		return 'KalturaGenericDistributionProviderFilter';
	}
	
	public function __construct(SimpleXMLElement $xml = null)
	{
		parent::__construct($xml);
		
		if(is_null($xml))
			return;
		
	}

}

