<?php
/**
 * @package Admin
 * @subpackage Client
 */
class Kaltura_Client_DailymotionDistribution_Type_DailymotionDistributionProfileFilter extends Kaltura_Client_DailymotionDistribution_Type_DailymotionDistributionProfileBaseFilter
{
	public function getKalturaObjectType()
	{
		return 'KalturaDailymotionDistributionProfileFilter';
	}
	
	public function __construct(SimpleXMLElement $xml = null)
	{
		parent::__construct($xml);
		
		if(is_null($xml))
			return;
		
	}

}

