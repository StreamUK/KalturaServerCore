<?php
/**
 * @package Admin
 * @subpackage Client
 */
class Kaltura_Client_ContentDistribution_Type_DistributionProviderFilter extends Kaltura_Client_ContentDistribution_Type_DistributionProviderBaseFilter
{
	public function getKalturaObjectType()
	{
		return 'KalturaDistributionProviderFilter';
	}
	
	public function __construct(SimpleXMLElement $xml = null)
	{
		parent::__construct($xml);
		
		if(is_null($xml))
			return;
		
	}

}

