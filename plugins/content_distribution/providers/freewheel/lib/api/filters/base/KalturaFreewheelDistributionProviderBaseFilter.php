<?php
/**
 * @package plugins.freewheelDistribution
 * @subpackage api.filters.base
 * @abstract
 */
abstract class KalturaFreewheelDistributionProviderBaseFilter extends KalturaDistributionProviderFilter
{
	static private $map_between_objects = array
	(
	);

	static private $order_by_map = array
	(
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), KalturaFreewheelDistributionProviderBaseFilter::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), KalturaFreewheelDistributionProviderBaseFilter::$order_by_map);
	}
}
