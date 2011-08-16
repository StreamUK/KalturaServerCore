<?php
/**
 * @package Admin
 * @subpackage Client
 */
class Kaltura_Client_ContentDistribution_Type_SyndicationDistributionProviderFilter extends Kaltura_Client_ContentDistribution_Type_SyndicationDistributionProviderBaseFilter
{
	public function getKalturaObjectType()
	{
		return 'KalturaSyndicationDistributionProviderFilter';
	}
	
	public function __construct(SimpleXMLElement $xml = null)
	{
		parent::__construct($xml);
		
		if(is_null($xml))
			return;
		
	}

}

