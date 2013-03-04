<?php
/**
 * @package plugins.freewheelGenericDistribution
 * @subpackage api.filters.base
 * @abstract
 */
abstract class KalturaFreewheelGenericDistributionProviderBaseFilter extends KalturaDistributionProviderFilter
{
	static private $map_between_objects = array
	(
	);

	static private $order_by_map = array
	(
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), KalturaFreewheelGenericDistributionProviderBaseFilter::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), KalturaFreewheelGenericDistributionProviderBaseFilter::$order_by_map);
	}
}
