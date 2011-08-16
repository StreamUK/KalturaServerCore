<?php
/**
 * @package Admin
 * @subpackage Client
 */
abstract class Kaltura_Client_DailymotionDistribution_Type_DailymotionDistributionProfileBaseFilter extends Kaltura_Client_ContentDistribution_Type_DistributionProfileFilter
{
	public function getKalturaObjectType()
	{
		return 'KalturaDailymotionDistributionProfileBaseFilter';
	}
	
	public function __construct(SimpleXMLElement $xml = null)
	{
		parent::__construct($xml);
		
		if(is_null($xml))
			return;
		
	}

}

