<?php
/**
 * @package plugins.ndnDistribution
 * @subpackage api.filters.base
 * @abstract
 */
abstract class KalturaNdnDistributionProfileBaseFilter extends KalturaConfigurableDistributionProfileFilter
{
	static private $map_between_objects = array
	(
	);

	static private $order_by_map = array
	(
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), KalturaNdnDistributionProfileBaseFilter::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), KalturaNdnDistributionProfileBaseFilter::$order_by_map);
	}
}
