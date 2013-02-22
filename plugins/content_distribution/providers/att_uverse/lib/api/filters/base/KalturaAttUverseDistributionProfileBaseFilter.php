<?php
/**
 * @package plugins.attUverseDistribution
 * @subpackage api.filters.base
 * @abstract
 */
abstract class KalturaAttUverseDistributionProfileBaseFilter extends KalturaConfigurableDistributionProfileFilter
{
	static private $map_between_objects = array
	(
	);

	static private $order_by_map = array
	(
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), KalturaAttUverseDistributionProfileBaseFilter::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), KalturaAttUverseDistributionProfileBaseFilter::$order_by_map);
	}
}
