<?php
/**
 * @package plugins.quickPlayDistribution
 * @subpackage api.filters.base
 * @abstract
 */
abstract class KalturaQuickPlayDistributionProviderBaseFilter extends KalturaDistributionProviderFilter
{
	static private $map_between_objects = array
	(
	);

	static private $order_by_map = array
	(
	);

	public function getMapBetweenObjects()
	{
		return array_merge(parent::getMapBetweenObjects(), KalturaQuickPlayDistributionProviderBaseFilter::$map_between_objects);
	}

	public function getOrderByMap()
	{
		return array_merge(parent::getOrderByMap(), KalturaQuickPlayDistributionProviderBaseFilter::$order_by_map);
	}
}
